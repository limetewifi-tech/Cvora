<?php

namespace Tests\Feature;

use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_a_resume_can_be_created_updated_and_deleted(): void
    {
        $user = $this->makeUser('premium');
        $template = \App\Models\ResumeTemplate::query()->where('slug', 'moderne')->firstOrFail();

        $this->actingAs($user)->post(route('cvs.store'), [
            'title' => 'Candidature banque',
            'template_id' => $template->id,
        ])->assertRedirect();

        $resume = $user->resumes()->first();
        $this->assertNotNull($resume);
        $this->assertSame('Amina', $resume->personal['first_name']);
        $this->assertSame('Analyste à Kinshasa.', $resume->summary);

        $this->put(route('cvs.update', $resume), $this->resumeForm($resume, [
            'title' => 'Candidature actualisée',
            'summary' => 'Analyste financière à Kinshasa.',
        ]))->assertRedirect(route('cvs.edit', $resume));

        $resume->refresh();
        $this->assertSame('Candidature actualisée', $resume->title);
        $this->assertSame('Analyste financière à Kinshasa.', $resume->summary);
        $this->assertSame(1, $resume->lock_version);

        $this->delete(route('cvs.destroy', $resume), ['confirm' => '1'])
            ->assertRedirect(route('cvs.index'));

        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
    }

    public function test_stale_autosave_is_rejected(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->putJson(route('cvs.update', $resume), $this->resumeForm($resume, [
            'lock_version' => 99,
            'title' => 'Conflit',
        ]))->assertStatus(409);

        $this->assertSame('CV principal', $resume->fresh()->title);
    }

    public function test_free_plan_blocks_a_second_resume_and_a_premium_template(): void
    {
        $user = $this->makeUser();
        $this->makeResume($user);
        $premium = \App\Models\ResumeTemplate::query()->where('slug', 'technique')->firstOrFail();

        $this->actingAs($user)->post(route('cvs.store'), [
            'title' => 'Second CV',
            'template_id' => \App\Models\ResumeTemplate::query()->where('slug', 'classique')->value('id'),
        ])->assertSessionHas('error');

        $this->assertSame(1, $user->resumes()->count());

        $user->resumes()->delete();

        $this->post(route('cvs.store'), [
            'title' => 'CV technique',
            'template_id' => $premium->id,
        ])->assertSessionHas('error');

        $this->assertSame(0, $user->resumes()->count());
    }

    public function test_create_page_restores_the_template_catalog_when_it_is_empty(): void
    {
        \App\Models\ResumeTemplate::query()->delete();
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('cvs.create'))
            ->assertOk()
            ->assertSee('Choisissez un modèle')
            ->assertSee('Moderne')
            ->assertSee('Classique')
            ->assertSee('Étudiant')
            ->assertSee('International')
            ->assertSee('Inclus')
            ->assertSee('Premium')
            ->assertDontSee('Le champ template id est obligatoire.');

        $this->assertSame(8, \App\Models\ResumeTemplate::query()->count());

        $this->post(route('cvs.store'), ['title' => 'Sans modèle'])
            ->assertSessionHasErrors([
                'template_id' => 'Choisissez un modèle.',
            ]);
    }

    public function test_an_account_without_a_plan_receives_the_free_plan_and_can_create_a_resume(): void
    {
        $user = $this->makeUser();
        $user->subscription()->delete();
        $template = \App\Models\ResumeTemplate::query()->where('slug', 'moderne')->firstOrFail();

        $this->actingAs($user)->get(route('cvs.create'))
            ->assertOk()
            ->assertSee('L\'abonnement Gratuit est activé')
            ->assertSee('Moderne')
            ->assertSee('Abonnement Gratuit')
            ->assertDontSee('Aucune offre active');

        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);

        $this->post(route('cvs.store'), [
            'title' => 'CV de Amina',
            'template_id' => $template->id,
        ])->assertRedirect();

        $this->assertSame(1, $user->resumes()->count());
        $this->assertSame(5, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_an_active_premium_plan_is_not_replaced(): void
    {
        $user = $this->makeUser('premium');
        $credits = (int) $user->subscription->credits_remaining;

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('L\'abonnement Gratuit est activé');

        $fresh = $user->fresh();
        $this->assertSame('premium', $fresh->subscription->plan->slug);
        $this->assertSame($credits, (int) $fresh->subscription->credits_remaining);
    }

    public function test_dashboard_counts_come_from_the_signed_in_user(): void
    {
        $user = $this->makeUser('premium');
        $other = $this->makeUser('premium');
        $this->makeResume($user);
        $this->makeResume($other, ['title' => 'CV d\'une autre personne']);
        $user->coverLetters()->create([
            'title' => 'Lettre locale',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => 'Corps de la lettre.',
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1')
            ->assertSee('Lettre locale')
            ->assertDontSee('CV d\'une autre personne');
    }
}
