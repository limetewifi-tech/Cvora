<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Services\SettingsRepository;
use Illuminate\Support\Facades\Log;

class AiRouter implements AiProvider
{
    public function __construct(private SettingsRepository $settings) {}

    public function name(): string
    {
        return $this->resolve($this->driverName())->name();
    }

    public function configured(): bool
    {
        return $this->resolve($this->driverName())->configured();
    }

    public function driverName(): string
    {
        return (string) $this->settings->get('ai_driver', config('cvora.ai.driver'));
    }

    public function fallbackName(): string
    {
        return (string) $this->settings->get('ai_fallback_driver', (string) config('cvora.ai.fallback'));
    }

    public function complete(string $system, string $userPayload): array
    {
        $primary = $this->resolve($this->driverName());

        if (! $primary->configured()) {
            throw new AiException('Le fournisseur d\'IA n\'est pas configuré. Ajoutez une clé API et un modèle dans l\'environnement du serveur.');
        }

        try {
            return $primary->complete($system, $userPayload);
        } catch (AiException $exception) {
            $fallback = $this->fallback();

            if ($fallback === null) {
                throw $exception;
            }

            Log::warning('Repli vers un autre fournisseur IA', [
                'provider' => $primary->name(),
                'fallback' => $fallback->name(),
            ]);

            return $fallback->complete($system, $userPayload);
        }
    }

    private function fallback(): ?AiProvider
    {
        $name = $this->fallbackName();

        if ($name === '' || $name === $this->driverName() || $name === 'fake') {
            return null;
        }

        $provider = $this->resolve($name);

        return $provider->configured() ? $provider : null;
    }

    private function resolve(string $name): AiProvider
    {
        return match ($name) {
            'fake' => new FakeAiProvider,
            'anthropic' => app(AnthropicProvider::class),
            'gemini' => app(GeminiProvider::class),
            'groq' => app(GroqProvider::class),
            default => new UnconfiguredAiProvider,
        };
    }
}
