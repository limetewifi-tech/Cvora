<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Http;

class AnthropicProvider extends RemoteAiProvider
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function complete(string $system, string $userPayload): array
    {
        if (! $this->configured()) {
            $this->unconfigured();
        }

        $base = $this->baseUrl();
        $endpoint = str_ends_with($base, '/v1') ? $base.'/messages' : $base.'/v1/messages';

        try {
            $response = Http::timeout($this->timeout())
                ->connectTimeout(5)
                ->withHeaders([
                    'x-api-key' => $this->apiKey(),
                    'anthropic-version' => '2023-06-01',
                ])
                ->acceptJson()
                ->post($endpoint, [
                    'model' => $this->model(),
                    'max_tokens' => 4096,
                    'temperature' => 0.2,
                    'system' => $system,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPayload],
                    ],
                ]);
        } catch (AiException $exception) {
            throw $exception;
        } catch (\Throwable) {
            $this->timedOut();
        }

        if (! $response->successful()) {
            $this->fail($response->status());
        }

        return $this->decode($response->json('content.0.text'));
    }
}
