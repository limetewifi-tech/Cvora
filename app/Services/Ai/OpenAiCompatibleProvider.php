<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Services\SettingsRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiCompatibleProvider implements AiProvider
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
        $model = (string) ($this->settings->get('ai_model') ?: config('cvora.ai.model'));
        $timeout = max(5, (int) config('cvora.ai.timeout'));

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(5)
                ->withToken((string) config('cvora.ai.api_key'))
                ->acceptJson()
                ->post($base.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
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

        $content = $response->json('choices.0.message.content');

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
