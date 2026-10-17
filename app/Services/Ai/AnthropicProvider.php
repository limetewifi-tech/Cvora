<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Services\SettingsRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicProvider implements AiProvider
{
    public function __construct(private SettingsRepository $settings) {}

    public function configured(): bool
    {
        return filled(config('cvora.ai.api_key'));
    }

    public function complete(string $system, string $userPayload): array
    {
        if (! $this->configured()) {
            throw new AiException('Le fournisseur d\'IA n\'est pas configuré. Ajoutez AI_API_KEY dans l\'environnement.');
        }

        $base = rtrim((string) ($this->settings->get('ai_base_url') ?: config('cvora.ai.base_url')), '/');

        if ($base === '' || str_contains($base, 'openai.com')) {
            $base = 'https://api.anthropic.com';
        }

        $model = (string) ($this->settings->get('ai_model') ?: config('cvora.ai.model'));

        if ($model === '' || str_starts_with($model, 'gpt-')) {
            $model = 'claude-sonnet-4-5';
        }

        $endpoint = str_ends_with($base, '/v1') ? $base.'/messages' : $base.'/v1/messages';
        $timeout = max(5, (int) config('cvora.ai.timeout'));

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => (string) config('cvora.ai.api_key'),
                    'anthropic-version' => '2023-06-01',
                ])
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $model,
                    'max_tokens' => 4096,
                    'temperature' => 0.2,
                    'system' => $system,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPayload],
                    ],
                ]);
        } catch (\Throwable) {
            throw new AiException('Le fournisseur d\'IA n\'a pas répondu à temps. Réessayez dans un instant.');
        }

        if (! $response->successful()) {
            Log::warning('Échec du fournisseur IA', ['status' => $response->status()]);

            throw new AiException('Le fournisseur d\'IA a renvoyé une erreur. Aucun crédit n\'est conservé.');
        }

        $content = $response->json('content.0.text');

        if (! is_string($content)) {
            throw new AiException('La réponse du fournisseur IA est illisible.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) && preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
        }

        if (! is_array($decoded)) {
            throw new AiException('La réponse du fournisseur IA n\'est pas un JSON exploitable.');
        }

        return $decoded;
    }
}
