<?php

namespace Tests\Feature;

use App\Services\Ai\AiCareerService;
use App\Services\Ai\AiProvider;
use App\Services\Ai\AiRouter;
use App\Services\Documents\ResumePresenter;
use Illuminate\Support\Facades\Http;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class ResumeCoachTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_the_editor_shows_an_a4_preview_and_a_completion_list(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->get(route('cvs.edit', $resume))
            ->assertOk()
            ->assertSee('Format A4')
            ->assertSee('Aperçu A4')
            ->assertSee('7 éléments prêts sur 7')
            ->assertSee('Conversation')
            ->assertSee('Améliorer ce texte')
            ->assertSee('Corriger l\'orthographe', false)
            ->assertSee('Rendre le français plus clair')
            ->assertSee('Afficher seulement ce qui reste')
            ->assertSee('Cette partie est déjà remplie')
            ->assertSee('Retenir ce modèle')
            ->assertSee('cadre=1', false);

        $this->get(route('cvs.preview', $resume))
            ->assertOk()
            ->assertSee('210mm')
            ->assertSee('297mm')
            ->assertSee('Format A4 · 210 × 297 mm')
            ->assertSee('Imprimer ou enregistrer en PDF');

        $this->get(route('cvs.preview', $resume).'?cadre=1')
            ->assertOk()
            ->assertSee('210mm')
            ->assertDontSee('Imprimer ou enregistrer en PDF');

        $print = view('resumes.paper', [
            'document' => app(ResumePresenter::class)->present($resume),
            'title' => $resume->title,
        ])->render();

        $this->assertStringContainsString('size: A4 portrait', $print);
        $this->assertStringContainsString('margin: 14mm 12mm', $print);
        $this->assertStringContainsString('print-color-adjust: exact', $print);
        $this->assertStringNotContainsString('Imprimer ou enregistrer en PDF', $print);
    }

    public function test_proofreading_returns_the_existing_text_and_leaves_the_resume_unchanged(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->postJson(route('cvs.proofread', $resume), [
            'mode' => 'spelling',
        ])->assertOk()
            ->assertJsonPath('successful', true)
            ->assertJsonPath('suggestions.0.target', 'summary')
            ->assertJsonPath('suggestions.0.text', 'Analyste à Kinshasa.')
            ->assertJsonPath('suggestions.1.text', 'Suivi des dossiers clients.')
            ->assertJsonPath('credits_remaining', 4);

        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        $this->assertSame(4, (int) $user->fresh()->subscription->credits_remaining);
        $this->assertContains('throttle:ai', app('router')->getRoutes()->getByName('cvs.proofread')->gatherMiddleware());
    }

    public function test_proofreading_refuses_an_empty_resume_without_spending_a_credit(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user, ['summary' => '']);
        $content = $resume->content;
        $content['experiences'][0]['description'] = '';
        $content['education'][0]['description'] = '';
        $resume->forceFill(['content' => $content])->save();

        $this->actingAs($user)->postJson(route('cvs.proofread', $resume), [
            'mode' => 'clarity',
        ])->assertStatus(422)
            ->assertJsonPath('successful', false);

        $this->assertSame(5, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_the_conversation_proposes_only_existing_facts_and_leaves_the_resume_unchanged(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user, ['summary' => 'Analyste à Kinshasa.']);

        $this->actingAs($user)->postJson(route('cvs.coach', $resume), [
            'message' => 'Propose une formulation du résumé à partir des faits déjà saisis.',
        ])->assertOk()
            ->assertJsonPath('successful', true)
            ->assertJsonPath('suggestions.0.target', 'summary')
            ->assertJsonPath('suggestions.0.text', 'Analyste à Kinshasa.')
            ->assertJsonPath('credits_remaining', 4);

        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        $this->assertSame(4, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_the_conversation_does_not_invent_a_missing_summary(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user, ['summary' => '']);

        $this->actingAs($user)->postJson(route('cvs.coach', $resume), [
            'message' => 'Rédige mon résumé professionnel.',
        ])->assertOk()
            ->assertJsonPath('suggestions', [])
            ->assertJsonPath('missing.0', 'Résumé professionnel');

        $response = $this->postJson(route('cvs.coach', $resume), [
            'message' => 'Rédige mon résumé professionnel.',
        ])->assertOk();

        $this->assertStringContainsString('Je ne complète pas', (string) $response->json('reply'));
        $this->assertSame('', (string) $resume->fresh()->summary);
        $this->assertSame(3, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_an_empty_message_and_another_users_resume_are_refused_without_spending_a_credit(): void
    {
        $owner = $this->makeUser();
        $resume = $this->makeResume($owner);
        $other = $this->makeUser();

        $this->actingAs($owner)->postJson(route('cvs.coach', $resume), [
            'message' => '   ',
        ])->assertStatus(422);

        $this->assertSame(5, (int) $owner->fresh()->subscription->credits_remaining);

        $this->actingAs($other)->postJson(route('cvs.coach', $resume), [
            'message' => 'Propose une formulation du résumé.',
        ])->assertNotFound();

        $this->assertContains('throttle:ai', app('router')->getRoutes()->getByName('cvs.coach')->gatherMiddleware());
    }

    public function test_an_unknown_driver_does_not_spend_a_credit_on_the_conversation(): void
    {
        Http::fake();
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        config([
            'cvora.ai.driver' => 'openai',
            'cvora.ai.fallback' => 'anthropic',
            'cvora.ai.providers.anthropic.api_key' => 'anthropic-test-key',
            'cvora.ai.providers.anthropic.model' => 'modele-anthropic',
            'cvora.ai.providers.anthropic.base_url' => 'https://api.anthropic.test',
        ]);
        $this->app->forgetInstance(AiProvider::class);
        $this->app->forgetInstance(AiRouter::class);
        $this->app->forgetInstance(AiCareerService::class);

        $this->actingAs($user)->postJson(route('cvs.coach', $resume), [
            'message' => 'Propose une formulation du résumé.',
        ])->assertStatus(422)
            ->assertJsonPath('successful', false);

        $this->assertSame(5, (int) $user->fresh()->subscription->credits_remaining);
        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        Http::assertNothingSent();
    }
}
