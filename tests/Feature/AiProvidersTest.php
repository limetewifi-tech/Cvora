<?php

namespace Tests\Feature;

use App\Exceptions\AiException;
use App\Services\Ai\AiProvider;
use App\Services\Ai\AiRouter;
use App\Services\Ai\AnthropicProvider;
use App\Services\Ai\GeminiProvider;
use App\Services\Ai\GroqProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiProvidersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_anthropic_gemini_and_groq_normalize_simulated_json(): void
    {
        config([
            'cvora.ai.timeout' => 10,
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
            'cvora.ai.providers.gemini.api_key' => 'gemini-test-key',
            'cvora.ai.providers.gemini.model' => 'modele-gemini',
            'cvora.ai.providers.gemini.base_url' => 'https://generativelanguage.googleapis.test',
            'cvora.ai.providers.groq.api_key' => 'groq-test-key',
            'cvora.ai.providers.groq.model' => 'modele-groq',
            'cvora.ai.providers.groq.base_url' => 'https://api.groq.test/openai/v1',
        ]);

        Http::fake([
            'https://api.anthropic.test/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => '{"proposal":"Texte Claude"}'],
                ],
            ]),
            'https://generativelanguage.googleapis.test/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '{"proposal":"Texte Gemini"}']]]],
                ],
            ]),
            'https://api.groq.test/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"proposal":"Texte Groq"}']],
                ],
            ]),
        ]);

        $this->assertSame('Texte Claude', app(AnthropicProvider::class)->complete('systeme', 'donnees')['proposal']);
        $this->assertSame('Texte Gemini', app(GeminiProvider::class)->complete('systeme', 'donnees')['proposal']);
        $this->assertSame('Texte Groq', app(GroqProvider::class)->complete('systeme', 'donnees')['proposal']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.test/v1/messages'
                && $request->hasHeader('x-api-key', 'anthropic-test-key')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['model'] === 'modele-anthropic'
                && $request['system'] === 'systeme';
        });
        Http::assertSent(function ($request) {
            return $request->url() === 'https://generativelanguage.googleapis.test/v1beta/models/modele-gemini:generateContent'
                && $request->hasHeader('x-goog-api-key', 'gemini-test-key')
                && $request['generationConfig']['responseMimeType'] === 'application/json'
                && ! str_contains($request->url(), 'gemini-test-key');
        });
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.groq.test/openai/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer groq-test-key')
                && $request['model'] === 'modele-groq'
                && $request['response_format']['type'] === 'json_object';
        });
    }

    public function test_a_missing_model_does_not_call_the_provider(): void
    {
        config([
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => '',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
        ]);

        try {
            app(AnthropicProvider::class)->complete('systeme', 'donnees');
            $this->fail('Un modèle vide doit refuser l\'appel.');
        } catch (AiException $exception) {
            $this->assertStringNotContainsString('anthropic-test-key', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_rate_limit_errors_hide_the_response_body(): void
    {
        config([
            'cvora.ai.providers.gemini.api_key' => 'gemini-test-key',
            'cvora.ai.providers.gemini.model' => 'modele-gemini',
            'cvora.ai.providers.gemini.base_url' => 'https://generativelanguage.googleapis.test',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.test/*' => Http::response([
                'error' => 'secret-body-marker gemini-test-key',
            ], 429),
        ]);

        try {
            app(GeminiProvider::class)->complete('systeme', 'donnees');
            $this->fail('Une limite de débit doit lever une exception.');
        } catch (AiException $exception) {
            $this->assertStringContainsString('limite de débit', $exception->getMessage());
            $this->assertStringNotContainsString('secret-body-marker', $exception->getMessage());
            $this->assertStringNotContainsString('gemini-test-key', $exception->getMessage());
        }
    }

    public function test_fallback_runs_once_and_only_after_a_configured_primary_fails(): void
    {
        config([
            'cvora.ai.driver' => 'anthropic',
            'cvora.ai.fallback' => 'groq',
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
            'cvora.ai.providers.groq.api_key' => 'groq-test-key',
            'cvora.ai.providers.groq.model' => 'modele-groq',
            'cvora.ai.providers.groq.base_url' => 'https://api.groq.test/openai/v1',
        ]);

        Http::fake([
            'https://api.anthropic.test/*' => Http::response(['error' => 'unavailable'], 503),
            'https://api.groq.test/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"proposal":"Repli"}']],
                ],
            ]),
        ]);

        $result = app(AiRouter::class)->complete('systeme', 'donnees');

        $this->assertSame('Repli', $result['proposal']);
        Http::assertSentCount(2);
    }

    public function test_a_successful_response_does_not_call_the_fallback(): void
    {
        config([
            'cvora.ai.driver' => 'anthropic',
            'cvora.ai.fallback' => 'groq',
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
            'cvora.ai.providers.groq.api_key' => 'groq-test-key',
            'cvora.ai.providers.groq.model' => 'modele-groq',
            'cvora.ai.providers.groq.base_url' => 'https://api.groq.test/openai/v1',
        ]);

        Http::fake([
            'https://api.anthropic.test/*' => Http::response([
                'content' => [
                    ['type' => 'text', 'text' => '{"proposal":"Principal"}'],
                ],
            ]),
            'https://api.groq.test/*' => Http::response(['error' => 'should-not-run'], 500),
        ]);

        $this->assertSame('Principal', app(AiRouter::class)->complete('systeme', 'donnees')['proposal']);
        Http::assertSentCount(1);
    }

    public function test_fallback_to_the_same_provider_does_not_loop(): void
    {
        config([
            'cvora.ai.driver' => 'anthropic',
            'cvora.ai.fallback' => 'anthropic',
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
        ]);

        Http::fake([
            'https://api.anthropic.test/*' => Http::response(['error' => 'unavailable'], 500),
        ]);

        $this->expectException(AiException::class);
        try {
            app(AiRouter::class)->complete('systeme', 'donnees');
        } finally {
            Http::assertSentCount(1);
        }
    }

    public function test_an_unconfigured_primary_does_not_call_the_fallback(): void
    {
        config([
            'cvora.ai.driver' => 'gemini',
            'cvora.ai.fallback' => 'groq',
            'cvora.ai.providers.gemini.api_key' => '',
            'cvora.ai.providers.gemini.model' => '',
            'cvora.ai.providers.groq.api_key' => 'groq-test-key',
            'cvora.ai.providers.groq.model' => 'modele-groq',
            'cvora.ai.providers.groq.base_url' => 'https://api.groq.test/openai/v1',
        ]);

        try {
            app(AiRouter::class)->complete('systeme', 'donnees');
            $this->fail('Un fournisseur non configuré ne doit pas basculer.');
        } catch (AiException $exception) {
            $this->assertStringNotContainsString('groq-test-key', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_status_command_does_not_reveal_secrets_or_call_a_provider(): void
    {
        config([
            'cvora.ai.driver' => 'anthropic',
            'cvora.ai.fallback' => '',
            'cvora.ai.providers.anthropic.api_key' => 'super-secret-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.gemini.api_key' => '',
            'cvora.ai.providers.gemini.model' => '',
            'cvora.ai.providers.groq.api_key' => '',
            'cvora.ai.providers.groq.model' => '',
        ]);

        $this->artisan('cvora:ai-status')
            ->expectsOutputToContain('anthropic : clé présente, modèle modele-anthropic')
            ->expectsOutputToContain('Aucun appel distant')
            ->doesntExpectOutputToContain('super-secret-key')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_fake_driver_is_refused_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['cvora.ai.driver' => 'fake']);
        $this->app->forgetInstance(AiProvider::class);

        $this->expectException(\RuntimeException::class);
        app(AiProvider::class);
    }
}
