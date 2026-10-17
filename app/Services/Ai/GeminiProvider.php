<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Http;

class GeminiProvider extends RemoteAiProvider
{
    public function name(): string
    {
        return 'gemini';
    }

    public function complete(string $system, string $userPayload): array
    {
        if (! $this->configured()) {
            $this->unconfigured();
        }

        $base = $this->baseUrl();
        $prefix = str_ends_with($base, '/v1beta') ? $base : $base.'/v1beta';
        $endpoint = $prefix.'/models/'.$this->model().':generateContent';

        try {
            $response = Http::timeout($this->timeout())
                ->connectTimeout(5)
                ->withHeaders(['x-goog-api-key' => $this->apiKey()])
                ->acceptJson()
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $system],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userPayload],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
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

        return $this->decode($this->text($response->json('candidates.0.content.parts')));
    }

    private function text(mixed $parts): ?string
    {
        if (! is_array($parts)) {
            return null;
        }

        foreach ($parts as $part) {
            if (is_array($part) && is_string($part['text'] ?? null)) {
                return $part['text'];
            }
        }

        return null;
    }
}
