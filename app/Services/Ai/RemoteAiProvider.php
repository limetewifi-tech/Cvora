<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Services\SettingsRepository;

abstract class RemoteAiProvider implements AiProvider
{
    public function __construct(protected SettingsRepository $settings) {}

    abstract public function name(): string;

    public function configured(): bool
    {
        return $this->apiKey() !== '' && $this->model() !== '' && $this->baseUrl() !== '';
    }

    protected function apiKey(): string
    {
        return trim((string) config('cvora.ai.providers.'.$this->name().'.api_key'));
    }

    protected function model(): string
    {
        $model = trim((string) ($this->settings->get($this->name().'_model') ?: config('cvora.ai.providers.'.$this->name().'.model')));

        if ($model === '' || ! preg_match('/^[A-Za-z0-9._-]{1,40}(?:\/[A-Za-z0-9._-]{1,39})?$/', $model)) {
            return '';
        }

        $key = $this->apiKey();

        if ($key !== '' && str_contains($model, $key)) {
            return '';
        }

        return $model;
    }

    protected function baseUrl(): string
    {
        $base = rtrim(trim((string) config('cvora.ai.providers.'.$this->name().'.base_url')), '/');
        $parts = parse_url($base);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ! isset($parts['host'])) {
            return '';
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return '';
        }

        return $base;
    }

    protected function timeout(): int
    {
        return max(5, (int) config('cvora.ai.timeout'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(mixed $content): array
    {
        return CompletionParser::decode($content);
    }

    protected function unconfigured(): never
    {
        throw new AiException('Le fournisseur '.$this->name().' n\'est pas configuré. Renseignez sa clé API et son modèle dans l\'environnement.');
    }

    protected function fail(int $status): never
    {
        ProviderHttp::fail($this->name(), $status);
    }

    protected function timedOut(): never
    {
        ProviderHttp::timedOut($this->name());
    }
}
