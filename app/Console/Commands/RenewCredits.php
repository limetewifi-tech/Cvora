<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Console\Command;

class RenewCredits extends Command
{
    protected $signature = 'cvora:renew-credits';

    protected $description = 'Renouvelle les crédits IA des abonnements dont la période est échue';

    public function handle(): int
    {
        $count = 0;

        Subscription::query()
            ->with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('credits_reset_at')
            ->where('credits_reset_at', '<=', now())
            ->orderBy('id')
            ->each(function (Subscription $subscription) use (&$count) {
                $subscription->forceFill([
                    'credits_remaining' => (int) ($subscription->plan?->ai_credits ?? 0),
                    'credits_reset_at' => now()->addMonth(),
                ])->save();
                $count++;
            });

        $this->info($count.' abonnement(s) renouvelé(s).');

        return self::SUCCESS;
    }
}
