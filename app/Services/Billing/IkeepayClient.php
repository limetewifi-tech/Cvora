<?php

namespace App\Services\Billing;

use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client HTTP du paiement direct iKeePay (H2H).
 * Le corps des réponses n'est ni journalisé ni renvoyé : il peut contenir
 * un numéro de téléphone. Aucune méthode de cette classe ne marque un paiement réussi.
 */
class IkeepayClient
{
    public function configured(): bool
    {
        return $this->apiKey() !== '' && $this->baseUrl() !== '';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function payin(array $payload): array
    {
        return $this->send('post', '/h2h-payin', $payload, (string) ($payload['external_reference'] ?? ''));
    }

    /**
     * @return array{status: string, amount: mixed, currency: string, provider_reference: string, external_reference: string, type: string}
     */
    public function verify(string $reference): array
    {
        $json = $this->send('get', '/h2h-verify/'.rawurlencode($reference), [], $reference);
        $data = $json['data'] ?? null;

        if (($json['status'] ?? '') !== 'success' || ! is_array($data)) {
            throw new PaymentException('La vérification iKeePay est illisible. L\'offre n\'a pas été activée.');
        }

        return [
            'status' => (string) ($data['status'] ?? ''),
            'amount' => $data['amount'] ?? null,
            'currency' => (string) ($data['currency'] ?? ''),
            'provider_reference' => (string) ($data['provider_reference'] ?? ''),
            'external_reference' => (string) ($data['external_reference'] ?? ''),
            'type' => (string) ($data['type'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $payload, string $reference): array
    {
        if (! $this->configured()) {
            throw new PaymentException('iKeePay n\'est pas configuré. Renseignez IKEEPAY_API_KEY et IKEEPAY_API_URL.');
        }

        $url = $this->baseUrl().$path;

        try {
            $pending = Http::timeout(20)
                ->connectTimeout(5)
                ->withHeaders(['x-api-key' => $this->apiKey()])
                ->acceptJson();

            $response = $method === 'get'
                ? $pending->get($url)
                : $pending->asJson()->post($url, $payload);
        } catch (\Throwable $exception) {
            if ($exception instanceof PaymentException) {
                throw $exception;
            }

            Log::channel('payments')->error('iKeePay injoignable', ['reference' => $reference]);

            throw new PaymentException('Le prestataire de paiement est injoignable. Aucun débit n\'a été confirmé.');
        }

        Log::channel('payments')->info('Réponse iKeePay', [
            'reference' => $reference,
            'status' => $response->status(),
        ]);

        if (! $response->successful()) {
            throw new PaymentException('Le prestataire n\'a pas confirmé l\'opération. Aucun débit n\'a été validé.');
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new PaymentException('La réponse d\'iKeePay est illisible. L\'offre n\'a pas été activée.');
        }

        return $json;
    }

    private function apiKey(): string
    {
        return trim((string) config('cvora.payments.ikeepay.api_key'));
    }

    private function baseUrl(): string
    {
        $base = rtrim(trim((string) config('cvora.payments.ikeepay.api_url')), '/');
        $parts = parse_url($base);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ! isset($parts['host'])) {
            return '';
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return '';
        }

        return $base;
    }
}
