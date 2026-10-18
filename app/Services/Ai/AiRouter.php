<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Services\SettingsRepository;
use Illuminate\Support\Facades\Log;

class AiRouter implements AiProvider
{
    public const SUPPORTED = ['anthropic', 'gemini', 'groq'];

    public function __construct(private SettingsRepository $settings) {}

    public static function unsupportedMessage(string $name): string
    {
        return 'Le fournisseur « '.AiStatus::publicToken($name).' » n\'est pas pris en charge. Utilisez anthropic, gemini ou groq. Aucun autre fournisseur n\'est appelé.';
    }

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

    public function refusal(): ?string
    {
        $driver = $this->driverName();

        if (! in_array($driver, self::SUPPORTED, true) && $driver !== 'fake') {
            return self::unsupportedMessage($driver);
        }

        if (! $this->configured()) {
            $shown = AiStatus::publicToken($driver);

            return 'Le fournisseur « '.($shown !== '' ? $shown : 'sélectionné').' » n\'est pas configuré. Ajoutez sa clé API et son modèle dans l\'environnement du serveur. Aucun autre fournisseur n\'est appelé.';
        }

        return null;
    }

    public function complete(string $system, string $userPayload): array
    {
        if ($refusal = $this->refusal()) {
            throw new AiException($refusal);
        }

        $primary = $this->resolve($this->driverName());

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

        if ($name !== '') {
            if ($name === $this->driverName() || $name === 'fake' || ! in_array($name, self::SUPPORTED, true)) {
                return null;
            }

            $provider = $this->resolve($name);

            return $provider->configured() ? $provider : null;
        }

        foreach (self::SUPPORTED as $candidate) {
            if ($candidate === $this->driverName()) {
                continue;
            }

            $provider = $this->resolve($candidate);

            if ($provider->configured()) {
                return $provider;
            }
        }

        return null;
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
