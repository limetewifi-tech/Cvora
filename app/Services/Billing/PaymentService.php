<?php

namespace App\Services\Billing;

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\PaymentException;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\SubscriptionActivated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function assignPlanWithoutPayment(User $user, Plan $plan): Subscription
    {
        if (! $plan->is_active || ! $plan->isFree()) {
            throw new PaymentException('Seule une offre gratuite peut être activée sans paiement.');
        }

        return $this->activateSubscription($user, $plan, null);
    }

    public function startCheckout(User $user, Plan $plan): array
    {
        if (! $plan->is_active || $plan->isFree()) {
            throw new PaymentException('Cette offre ne nécessite pas de paiement.');
        }

        $transaction = $user->transactions()->create([
            'plan_id' => $plan->id,
            'subscription_id' => $user->subscription?->id,
            'reference' => 'cvora_'.Str::lower(Str::ulid()),
            'provider' => $this->gateway->driver(),
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => TransactionStatus::Pending,
        ]);

        try {
            $session = $this->gateway->start($user, $plan, $transaction);
        } catch (PaymentException $exception) {
            $transaction->forceFill([
                'status' => TransactionStatus::Failed,
                'error_message' => mb_substr($exception->getMessage(), 0, 180),
            ])->save();

            throw $exception;
        }

        return [
            'transaction' => $transaction->refresh(),
            'redirect_url' => $session['redirect_url'] ?? null,
        ];
    }

    public function confirmSandbox(Transaction $transaction, User $user): Transaction
    {
        if (config('cvora.payments.driver') !== 'sandbox' || $transaction->provider !== 'sandbox') {
            throw new PaymentException('La confirmation manuelle n\'existe qu\'en mode bac à sable.');
        }

        if ($transaction->user_id !== $user->id) {
            throw new PaymentException('Cette transaction ne vous appartient pas.');
        }

        return $this->complete($transaction, 'sandbox-'.$transaction->reference, [
            'source' => 'sandbox-ui',
        ]);
    }

    public function failSandbox(Transaction $transaction, User $user): Transaction
    {
        if (config('cvora.payments.driver') !== 'sandbox' || $transaction->provider !== 'sandbox') {
            throw new PaymentException('La confirmation manuelle n\'existe qu\'en mode bac à sable.');
        }

        if ($transaction->user_id !== $user->id) {
            throw new PaymentException('Cette transaction ne vous appartient pas.');
        }

        return DB::transaction(function () use ($transaction) {
            $locked = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== TransactionStatus::Pending) {
                return $locked;
            }

            $locked->forceFill([
                'status' => TransactionStatus::Failed,
                'error_message' => 'Paiement bac à sable marqué comme échoué.',
            ])->save();

            return $locked;
        });
    }

    public function handleWebhook(string $provider, string $rawBody, ?string $signature): Transaction
    {
        $secret = (string) config('cvora.payments.webhook_secret');
        $expected = hash_hmac('sha256', $rawBody, $secret);

        if ($secret === '' || ! hash_equals($expected, (string) $signature)) {
            Log::channel('payments')->warning('Signature de webhook refusée', ['provider' => $provider]);

            throw new PaymentException('Signature de notification invalide.');
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            throw new PaymentException('Notification illisible.');
        }

        $transaction = Transaction::query()->where('reference', $payload['reference'] ?? '')->first();

        if (! $transaction || $transaction->provider !== $provider) {
            throw new PaymentException('Transaction introuvable pour cette notification.');
        }

        if (($payload['status'] ?? '') === 'failed') {
            return DB::transaction(function () use ($transaction, $payload) {
                $locked = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

                if ($locked->status === TransactionStatus::Succeeded) {
                    return $locked;
                }

                $locked->forceFill([
                    'status' => TransactionStatus::Failed,
                    'error_message' => mb_substr((string) ($payload['error'] ?? 'Paiement refusé par le prestataire.'), 0, 180),
                    'payload' => $this->safePayload($payload),
                ])->save();

                return $locked;
            });
        }

        if (($payload['status'] ?? '') !== 'succeeded') {
            throw new PaymentException('Statut de notification non pris en charge.');
        }

        if (bccomp((string) ($payload['amount'] ?? ''), (string) $transaction->amount, 2) !== 0
            || strtoupper((string) ($payload['currency'] ?? '')) !== strtoupper($transaction->currency)) {
            Log::channel('payments')->error('Montant de webhook incohérent', ['reference' => $transaction->reference]);

            throw new PaymentException('Le montant confirmé ne correspond pas à la transaction.');
        }

        return $this->complete($transaction, (string) ($payload['provider_reference'] ?? ''), $this->safePayload($payload));
    }

    private function complete(Transaction $transaction, string $providerReference, array $payload): Transaction
    {
        return DB::transaction(function () use ($transaction, $providerReference, $payload) {
            $locked = Transaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === TransactionStatus::Succeeded) {
                return $locked;
            }

            if ($providerReference !== '') {
                $duplicate = Transaction::query()
                    ->where('provider_reference', $providerReference)
                    ->where('id', '!=', $locked->id)
                    ->exists();

                if ($duplicate) {
                    throw new PaymentException('Cette référence de paiement a déjà été traitée.');
                }
            }

            $locked->forceFill([
                'status' => TransactionStatus::Succeeded,
                'provider_reference' => $providerReference !== '' ? $providerReference : $locked->provider_reference,
                'payload' => $payload,
                'paid_at' => now(),
                'error_message' => null,
            ])->save();

            $locked->load(['user', 'plan']);
            $plan = $locked->plan;

            if ($plan && $locked->user) {
                $subscription = $this->activateSubscription($locked->user, $plan, $locked);
                $locked->forceFill(['subscription_id' => $subscription->id])->save();
            }

            return $locked->refresh();
        });
    }

    private function activateSubscription(User $user, Plan $plan, ?Transaction $transaction): Subscription
    {
        $subscription = Subscription::query()->firstOrNew(['user_id' => $user->id]);
        $subscription->fill([
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => $plan->isFree() ? null : now()->addMonth(),
            'credits_remaining' => (int) $plan->ai_credits,
            'credits_reset_at' => now()->addMonth(),
            'provider' => $transaction?->provider,
            'provider_reference' => $transaction?->provider_reference,
        ])->save();

        $user->notify(new SubscriptionActivated($plan->name));

        return $subscription;
    }

    private function safePayload(array $payload): array
    {
        unset($payload['api_key'], $payload['secret'], $payload['signature']);

        return array_intersect_key($payload, array_flip([
            'reference', 'provider_reference', 'status', 'amount', 'currency',
        ]));
    }
}
