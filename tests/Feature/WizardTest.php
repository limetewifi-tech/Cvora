<?php

namespace Tests\Feature;

use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class WizardTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_the_wizard_shows_one_step_and_keeps_the_full_editor(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->get(route('cvs.edit', $resume))
            ->assertOk()
            ->assertSee('Étape 1 sur 6')
            ->assertSee('Importer mon CV')
            ->assertSee('Modèle')
            ->assertSee('Police');

        $this->get(route('cvs.edit', ['resume' => $resume, 'complet' => 1]))
            ->assertOk()
            ->assertSee('Afficher seulement ce qui reste')
            ->assertDontSee('Étape 1 sur 6');

        $first = $this->get(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 1]));
        $first->assertOk()
            ->assertSee('Étape 1 sur 6')
            ->assertSee('Profil')
            ->assertSee('Indiquez vos informations personnelles.')
            ->assertSee('Importer mon CV')
            ->assertSee('Suivant')
            ->assertSee('Éditeur complet')
            ->assertSee('Aperçu A4')
            ->assertDontSee('Précédent');

        $html = $first->getContent();
        $this->assertStringContainsString('data-wizard-step="1">', $html);
        $this->assertStringContainsString('data-wizard-step="2" hidden', $html);
        $this->assertStringContainsString('wizard_step=2', $html);

        $second = $this->get(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 2]));
        $second->assertOk()
            ->assertSee('Étape 2 sur 6')
            ->assertSee('Expériences')
            ->assertSee('Précédent')
            ->assertDontSee('Importer mon CV');
        $this->assertStringContainsString('data-wizard-step="2">', $second->getContent());
        $this->assertStringContainsString('data-wizard-step="1" hidden', $second->getContent());

        $last = $this->get(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 99]));
        $last->assertOk()
            ->assertSee('Étape 6 sur 6')
            ->assertSee('Autres rubriques')
            ->assertSee('Modèle et couleurs')
            ->assertDontSee('Suivant');
        $this->assertStringContainsString('data-wizard-step="6">', $last->getContent());
        $this->assertStringContainsString('data-wizard-step="4" hidden', $last->getContent());
    }

    public function test_saving_from_a_wizard_step_returns_to_that_step(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->put(route('cvs.update', $resume), $this->resumeForm($resume, [
            'summary' => 'Analyste confirmée à Kinshasa.',
            'wizard' => '1',
            'wizard_step' => '5',
            'action' => 'add:languages',
        ]))->assertRedirect(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 5]));

        $this->assertSame('Analyste confirmée à Kinshasa.', $resume->fresh()->summary);
    }

    public function test_creating_a_resume_opens_the_first_wizard_step(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('start.store'), [
            'title' => 'CV guidé',
            'headline' => 'Analyste',
        ])->assertRedirect();

        $resume = $user->resumes()->where('title', 'CV guidé')->first();
        $this->assertNotNull($resume);

        $this->get(route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 1]))
            ->assertOk()
            ->assertSee('Étape 1 sur 6')
            ->assertSee('Analyste');
    }
}
