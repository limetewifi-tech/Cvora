<?php

namespace Tests\Feature;

use App\Models\ResumeTemplate;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class StartTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_the_start_page_shows_three_goals(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('start.index'))
            ->assertOk()
            ->assertSee('Créer un CV')
            ->assertSee('Améliorer un CV')
            ->assertSee('Répondre à une offre');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Créer un CV')
            ->assertSee('Choisir un modèle');
    }

    public function test_creating_from_the_start_page_uses_the_preferred_template_and_headline(): void
    {
        $user = $this->makeUser();
        $preferences = $user->profile->preferences;
        $preferences['default_template'] = 'classique';
        $user->profile->forceFill(['preferences' => $preferences])->save();
        $classique = ResumeTemplate::query()->where('slug', 'classique')->firstOrFail();

        $this->actingAs($user)->post(route('start.store'), [
            'title' => 'CV ciblé',
            'headline' => 'Développeur Laravel',
        ])->assertRedirect();

        $resume = $user->resumes()->first();
        $this->assertSame('CV ciblé', $resume->title);
        $this->assertSame($classique->id, $resume->template_id);
        $this->assertSame('Développeur Laravel', $resume->personal['headline']);
    }
}
