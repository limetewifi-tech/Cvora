<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Http;

class GroqProvider extends RemoteAiProvider
{
    public function name(): string
    {
        return 'groq';
    }

    public function complete(string $system, string $userPayload): array
    {
        if (! $this->configured()) {
            $this->unconfigured();
        }

        try {
            $response = Http::timeout($this->timeout())
                ->connectTimeout(5)
                ->withToken($this->apiKey())
                ->acceptJson()
                ->post($this->baseUrl().'/chat/completions', [
                    'model' => $this->model(),
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
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

        return $this->decode($response->json('choices.0.message.content'));
    }
}
