<?php

namespace App\Services\Billing;

use App\Exceptions\PaymentException;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;

class IkeepayGateway implements PaymentGateway
{
    public function __construct(private IkeepayClient $client) {}

    public function driver(): string
    {
        return 'ikeepay';
    }

    public function configured(): bool
    {
        return $this->client->configured();
    }

    public function start(User $user, Plan $plan, Transaction $transaction, array $details = []): array
    {
        if (! $this->configured()) {
            throw new PaymentException('iKeePay n\'est pas configuré. Renseignez IKEEPAY_API_KEY et IKEEPAY_API_URL.');
        }

        $phone = (string) ($details['phone_number'] ?? '');
        $country = strtoupper((string) ($details['country'] ?? ''));
        $operator = strtoupper((string) ($details['operator'] ?? ''));

        if (! preg_match('/^[0-9]{8,15}$/', $phone)
            || ! preg_match('/^[A-Z]{2}$/', $country)
            || ! preg_match('/^[A-Z0-9_]{2,20}$/', $operator)) {
            throw new PaymentException('Indiquez un numéro, un pays et un opérateur valides pour iKeePay.');
        }

        $json = $this->client->payin([
            'amount' => $this->amount($transaction),
            'currency' => strtoupper($transaction->currency),
            'country' => $country,
            'phoneNumber' => $phone,
            'operator' => $operator,
            'external_reference' => $transaction->reference,
            'customer_email' => (string) $user->email,
        ]);

        $providerReference = $this->nestedString($json, 'provider_reference');

        if ($providerReference !== null) {
            $transaction->forceFill([
                'provider_reference' => mb_substr($providerReference, 0, 120),
            ])->save();
        }

        $link = $this->paymentLink($json);

        return $link === null
            ? ['mode' => 'ikeepay']
            : ['mode' => 'ikeepay', 'redirect_url' => $link];
    }

    private function amount(Transaction $transaction): int|float
    {
        $normalized = number_format((float) $transaction->amount, 2, '.', '');

        return str_ends_with($normalized, '.00') ? (int) $normalized : (float) $normalized;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function paymentLink(array $json): ?string
    {
        foreach ([$json, $json['data'] ?? null, is_array($json['data'] ?? null) ? ($json['data']['data'] ?? null) : null] as $level) {
            if (! is_array($level)) {
                continue;
            }

            foreach (['payment_link', 'redirect_url', 'link'] as $key) {
                $value = $level[$key] ?? null;

                if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function nestedString(array $json, string $key): ?string
    {
        foreach ([$json, $json['data'] ?? null, is_array($json['data'] ?? null) ? ($json['data']['data'] ?? null) : null] as $level) {
            if (is_array($level) && is_string($level[$key] ?? null) && $level[$key] !== '') {
                return $level[$key];
            }
        }

        return null;
    }
}
