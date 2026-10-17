<?php

namespace Tests\Feature;

use App\Models\MatchAnalysis;
use App\Services\Ai\AiProvider;
use Illuminate\Support\Facades\Http;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class StudioTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_a_local_comparison_does_not_change_the_resume_or_spend_a_credit(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);
        $credits = $user->subscription->credits_remaining;

        $this->actingAs($user)->post(route('studio.store'), [
            'offer_title' => 'Analyste reporting',
            'company' => 'Banque exemple',
            'offer_text' => "Excel\nPython",
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertRedirect();

        $analysis = MatchAnalysis::query()->first();
        $this->assertNotNull($analysis);
        $this->assertSame(50, $analysis->score);
        $this->assertSame(['Excel'], $analysis->result['matched']);
        $this->assertSame(['Python'], $analysis->result['missing']);
        $this->assertSame('skipped', $analysis->ai_status);
        $this->assertNull($analysis->ai_suggestions);
        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        $this->assertSame($credits, $user->subscription()->first()->credits_remaining);
        $this->assertSame('Excel', $resume->fresh()->content['skills'][0]['name']);

        $this->actingAs($user)->get(route('studio.show', $analysis))
            ->assertOk()
            ->assertSee('Score local')
            ->assertSee('Python')
            ->assertDontSee('sk-');
    }

    public function test_a_comparison_requires_authorization_and_stays_private(): void
    {
        $owner = $this->makeUser();
        $resume = $this->makeResume($owner);

        $this->actingAs($owner)->post(route('studio.store'), [
            'offer_title' => 'Analyste',
            'offer_text' => 'Excel',
            'resume_id' => $resume->id,
        ])->assertSessionHasErrors('authorize_resume');
        $this->assertSame(0, MatchAnalysis::query()->count());

        $this->post(route('studio.store'), [
            'offer_title' => 'Analyste',
            'offer_text' => 'Excel',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertRedirect();

        $analysis = MatchAnalysis::query()->firstOrFail();
        $other = $this->makeUser();

        $this->actingAs($other)->get(route('studio.show', $analysis))->assertNotFound();
        $this->actingAs($other)->post(route('studio.apply', $analysis), [
            'confirm' => '1',
            'apply_summary' => '1',
        ])->assertNotFound();
        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
    }

    public function test_suggestions_are_applied_only_after_confirmation(): void
    {
        $user = $this->makeUser('pro');
        $resume = $this->makeResume($user);
        $analysis = $user->matchAnalyses()->create([
            'resume_id' => $resume->id,
            'offer_title' => 'Analyste',
            'offer_text' => 'Excel',
            'score' => 100,
            'result' => ['method' => 'test', 'score' => 100, 'matched' => ['Excel'], 'missing' => [], 'relevant_experiences' => [], 'explanation' => 'Test.'],
            'ai_status' => 'success',
            'ai_suggestions' => [
                'summary_proposal' => 'Analyste à Kinshasa.',
                'experiences' => [[
                    'index' => 0,
                    'description' => 'Suivi des dossiers clients, revu.',
                ]],
                'suggestions' => [],
                'matched_skills' => ['Excel'],
                'missing_skills' => [],
            ],
        ]);

        $this->actingAs($user)->post(route('studio.apply', $analysis), [
            'apply_summary' => '1',
            'experience_indexes' => [0],
        ])->assertSessionHasErrors('confirm');
        $this->assertSame('Suivi des dossiers clients.', $resume->fresh()->content['experiences'][0]['description']);

        $this->post(route('studio.apply', $analysis), [
            'confirm' => '1',
            'experience_indexes' => [0],
        ])->assertRedirect(route('cvs.edit', $resume));

        $fresh = $resume->fresh();
        $this->assertSame('Analyste à Kinshasa.', $fresh->summary);
        $this->assertSame('Suivi des dossiers clients, revu.', $fresh->content['experiences'][0]['description']);
        $this->assertSame('Excel', $fresh->content['skills'][0]['name']);
    }

    public function test_a_failed_provider_keeps_the_local_score_and_refunds_the_credit(): void
    {
        config([
            'cvora.ai.driver' => 'anthropic',
            'cvora.ai.fallback' => '',
            'cvora.ai.providers.anthropic.api_key' => 'studio-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-studio',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
        ]);
        $this->app->forgetInstance(AiProvider::class);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.anthropic.test/*' => Http::response(['error' => 'studio-secret-body'], 503),
        ]);

        $user = $this->makeUser('pro');
        $resume = $this->makeResume($user);
        $credits = $user->subscription->credits_remaining;

        $this->actingAs($user)->post(route('studio.store'), [
            'offer_title' => 'Analyste',
            'offer_text' => 'Excel',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
            'use_ai' => '1',
        ])->assertRedirect();

        $analysis = MatchAnalysis::query()->firstOrFail();
        $this->assertSame(100, $analysis->score);
        $this->assertSame('failed', $analysis->ai_status);
        $this->assertNull($analysis->ai_suggestions);
        $this->assertStringNotContainsString('studio-secret-body', (string) $analysis->ai_error);
        $this->assertStringNotContainsString('studio-test-key', (string) $analysis->ai_error);
        $this->assertSame($credits, $user->subscription()->first()->credits_remaining);
        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
    }
}
