<?php

namespace App\Services\Billing;

use App\Exceptions\PaymentException;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adaptateur d'intention pour un prestataire Mobile Money de type UniPay.
 * Le contrat HTTP ci-dessous doit être aligné sur la documentation marchande
 * officielle avant toute utilisation réelle. Aucun paiement n'est marqué
 * réussi dans cette classe.
 */
class UniPayGateway implements PaymentGateway
{
    public function driver(): string
    {
        return 'unipay';
    }

    public function configured(): bool
    {
        return filled(config('cvora.payments.unipay.api_url'))
            && filled(config('cvora.payments.unipay.api_key'))
            && filled(config('cvora.payments.unipay.merchant_id'));
    }

    public function start(User $user, Plan $plan, Transaction $transaction): array
    {
        if (! $this->configured()) {
            throw new PaymentException('UniPay n\'est pas configuré. Renseignez UNIPAY_API_URL, UNIPAY_API_KEY et UNIPAY_MERCHANT_ID.');
        }

        $url = rtrim((string) config('cvora.payments.unipay.api_url'), '/').'/v1/payments';

        try {
            $response = Http::timeout(20)
                ->withToken((string) config('cvora.payments.unipay.api_key'))
                ->acceptJson()
                ->post($url, [
                    'merchant_id' => config('cvora.payments.unipay.merchant_id'),
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'description' => 'Abonnement CVora '.$plan->name,
                    'callback_url' => route('payments.webhook', ['provider' => 'unipay']),
                    'return_url' => route('billing.index'),
                ]);
        } catch (\Throwable) {
            Log::channel('payments')->error('UniPay injoignable', ['reference' => $transaction->reference]);

            throw new PaymentException('Le prestataire de paiement est injoignable. Aucun débit n\'a été confirmé.');
        }

        if (! $response->successful() || ! filled($response->json('checkout_url'))) {
            Log::channel('payments')->error('Réponse UniPay refusée', [
                'reference' => $transaction->reference,
                'status' => $response->status(),
            ]);

            throw new PaymentException('Le prestataire n\'a pas ouvert de paiement. La transaction reste en attente.');
        }

        $transaction->forceFill([
            'provider_reference' => $response->json('provider_reference'),
        ])->save();

        return [
            'mode' => 'unipay',
            'redirect_url' => (string) $response->json('checkout_url'),
        ];
    }
}
