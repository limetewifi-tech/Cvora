<?php

namespace App\Services\Ai;

use App\Services\SettingsRepository;

class AiStatus
{
    public function __construct(private SettingsRepository $settings) {}

    /**
     * @return array{
     *     driver: string,
     *     fallback: string,
     *     remote_call: bool,
     *     providers: array<string, array{key_present: bool, model: string, configured: bool}>
     * }
     */
    public function snapshot(): array
    {
        $providers = [];

        foreach (['anthropic', 'gemini', 'groq'] as $name) {
            $key = trim((string) config('cvora.ai.providers.'.$name.'.api_key'));
            $model = trim((string) ($this->settings->get($name.'_model') ?: config('cvora.ai.providers.'.$name.'.model')));

            if ($model === '' || ! preg_match('/^[A-Za-z0-9._-]{1,80}$/', $model) || ($key !== '' && str_contains($model, $key))) {
                $model = '';
            }

            $providers[$name] = [
                'key_present' => $key !== '',
                'model' => $model,
                'configured' => $key !== '' && $model !== '',
            ];
        }

        return [
            'driver' => (string) $this->settings->get('ai_driver', config('cvora.ai.driver')),
            'fallback' => (string) $this->settings->get('ai_fallback_driver', (string) config('cvora.ai.fallback')),
            'providers' => $providers,
            'remote_call' => false,
        ];
    }
}
