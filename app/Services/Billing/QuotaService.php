<?php

namespace App\Services\Billing;

use App\Exceptions\PlanLimitException;
use App\Models\AiUsageLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SettingsRepository;
use Illuminate\Support\Facades\DB;

class QuotaService
{
    public function __construct(private SettingsRepository $settings) {}

    public function subscription(User $user): ?Subscription
    {
        $user->loadMissing('subscription');

        return $user->subscription;
    }

    public function plan(User $user): ?Plan
    {
        $user->loadMissing('subscription.plan');

        return $user->subscription?->plan;
    }

    public function moduleEnabled(string $flag): bool
    {
        return $this->settings->flag($flag, true);
    }

    public function allows(User $user, string $feature): bool
    {
        $plan = $this->plan($user);

        return $plan ? $plan->allows($feature) : false;
    }

    public function assertModule(string $flag, string $message): void
    {
        if (! $this->moduleEnabled($flag)) {
            throw new PlanLimitException($message);
        }
    }

    public function assertFeature(User $user, string $feature, string $message): void
    {
        if (! $this->allows($user, $feature)) {
            throw new PlanLimitException($message);
        }
    }

    public function assertCanCreateResume(User $user): void
    {
        $plan = $this->requirePlan($user);
        $limit = $plan->resume_limit;

        if ($limit !== null && $user->resumes()->count() >= $limit) {
            throw new PlanLimitException('Votre offre permet '.$limit.' CV. Passez à une offre supérieure pour en créer un autre.');
        }
    }

    public function assertCanCreateLetter(User $user): void
    {
        $this->assertModule('feature_letters', 'Les lettres de motivation sont temporairement désactivées.');
        $plan = $this->requirePlan($user);

        if (! $plan->allows('cover_letters')) {
            throw new PlanLimitException('Les lettres de motivation ne sont pas incluses dans votre offre.');
        }

        $limit = $plan->letter_limit;

        if ($limit !== null && $user->coverLetters()->count() >= $limit) {
            throw new PlanLimitException('Vous avez atteint le nombre de lettres prévu par votre offre.');
        }
    }

    public function assertCanUseTemplate(User $user, bool $premium): void
    {
        if ($premium && ! $this->allows($user, 'premium_templates')) {
            throw new PlanLimitException('Ce modèle est réservé aux offres Premium et Pro.');
        }
    }

    public function assertCanExport(User $user, string $format): void
    {
        $this->assertModule('feature_exports', 'Les exportations sont temporairement désactivées.');
        $feature = $format === 'docx' ? 'export_docx' : 'export_pdf';
        $label = $format === 'docx' ? 'Word' : 'PDF';

        $this->assertFeature($user, $feature, 'L\'exportation '.$label.' n\'est pas incluse dans votre offre.');
    }

    public function refreshCredits(Subscription $subscription): Subscription
    {
        $subscription->loadMissing('plan');

        if ($subscription->credits_reset_at && $subscription->credits_reset_at->isPast()) {
            $subscription->credits_remaining = (int) ($subscription->plan?->ai_credits ?? 0);
            $subscription->credits_reset_at = now()->addMonth();
            $subscription->save();
        }

        return $subscription;
    }

    /**
     * Réserve un crédit avant l'appel, et le restitue si l'appel échoue.
     */
    public function reserveAiCredit(User $user, string $feature): void
    {
        $this->assertModule('feature_ai', 'L\'assistant IA est temporairement désactivé.');

        $denied = null;

        DB::transaction(function () use ($user, &$denied) {
            $subscription = Subscription::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $subscription || ! $subscription->isActive()) {
                $denied = 'Aucune offre active n\'est associée à votre compte.';

                return;
            }

            $subscription->load('plan');
            $this->refreshCredits($subscription);

            if ($subscription->credits_remaining < 1) {
                $denied = 'Vous n\'avez plus de crédits IA pour cette période.';

                return;
            }

            $subscription->decrement('credits_remaining');
        });

        if ($denied) {
            $this->deny($user, $feature, $denied);
            throw new PlanLimitException($denied);
        }
    }

    public function refundAiCredit(User $user, string $feature, string $message): void
    {
        DB::transaction(function () use ($user) {
            $subscription = Subscription::query()->where('user_id', $user->id)->lockForUpdate()->first();
            $subscription?->increment('credits_remaining');
        });

        AiUsageLog::query()->create([
            'user_id' => $user->id,
            'feature' => $feature,
            'credits' => 0,
            'status' => 'error',
            'message' => mb_substr($message, 0, 180),
        ]);
    }

    public function logAiSuccess(User $user, string $feature, int $durationMs): int
    {
        AiUsageLog::query()->create([
            'user_id' => $user->id,
            'feature' => $feature,
            'credits' => 1,
            'status' => 'success',
            'duration_ms' => $durationMs,
        ]);

        return (int) $user->subscription()->value('credits_remaining');
    }

    public function creditsRemaining(User $user): int
    {
        $user->loadMissing('subscription');
        $subscription = $user->subscription;

        if (! $subscription) {
            return 0;
        }

        $this->refreshCredits($subscription);

        return (int) $subscription->credits_remaining;
    }

    private function requirePlan(User $user): Plan
    {
        $plan = $this->plan($user);

        if (! $plan || ! $user->subscription?->isActive()) {
            throw new PlanLimitException('Aucune offre active n\'est associée à votre compte.');
        }

        return $plan;
    }

    private function deny(User $user, string $feature, string $message): void
    {
        AiUsageLog::query()->create([
            'user_id' => $user->id,
            'feature' => $feature,
            'credits' => 0,
            'status' => 'denied',
            'message' => $message,
        ]);
    }
}
