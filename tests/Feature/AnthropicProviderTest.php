<?php

namespace Tests\Feature;

use App\Services\Ai\AnthropicProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnthropicProviderTest extends TestCase
{
    public function test_anthropic_provider_reads_a_json_message_without_calling_a_live_api(): void
    {
        config([
            'cvora.ai.api_key' => 'test-key',
            'cvora.ai.base_url' => 'https://api.openai.com/v1',
            'cvora.ai.model' => 'gpt-4o-mini',
            'cvora.ai.timeout' => 10,
        ]);

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => '{"proposal":"Texte relu"}'],
                ],
            ]),
        ]);

        $result = app(AnthropicProvider::class)->complete('systeme', '<donnees>parcours</donnees>');

        $this->assertSame('Texte relu', $result['proposal']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'test-key')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['model'] === 'claude-sonnet-4-5'
                && $request['system'] === 'systeme';
        });
    }
}
