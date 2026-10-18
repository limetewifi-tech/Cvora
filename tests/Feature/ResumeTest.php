<?php

namespace Tests\Feature;

use App\Models\ResumeTemplate;
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
        $template = ResumeTemplate::query()->where('slug', 'moderne')->firstOrFail();

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

    public function test_changing_the_template_keeps_the_text_and_only_changes_the_layout(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);
        $lateral = ResumeTemplate::query()->where('slug', 'experimente')->firstOrFail();

        $this->actingAs($user)->put(route('cvs.update', $resume), $this->resumeForm($resume, [
            'template_id' => $lateral->id,
            'summary' => 'Analyste à Kinshasa.',
            'theme' => ['primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'serif', 'size' => 'l'],
        ]))->assertRedirect();

        $resume->refresh();
        $this->assertSame($lateral->id, $resume->template_id);
        $this->assertSame('Analyste à Kinshasa.', $resume->summary);
        $this->assertSame('Banque exemple', $resume->content['experiences'][0]['company']);

        $this->get(route('cvs.preview', $resume))
            ->assertOk()
            ->assertSee('DejaVu Serif', false)
            ->assertSee('12pt', false)
            ->assertSee('layout-corporate', false)
            ->assertSee('Analyste à Kinshasa.');
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
        $premium = ResumeTemplate::query()->where('slug', 'technique')->firstOrFail();

        $this->actingAs($user)->post(route('cvs.store'), [
            'title' => 'Second CV',
            'template_id' => ResumeTemplate::query()->where('slug', 'classique')->value('id'),
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
        ResumeTemplate::query()->delete();
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('cvs.create'))
            ->assertOk()
            ->assertSee('Choisissez un modèle')
            ->assertSee('Moderne')
            ->assertSee('Classique')
            ->assertSee('Bleu professionnel')
            ->assertSee('Corporate')
            ->assertSee('Exécutif')
            ->assertSee('Élégant')
            ->assertSee('Inclus')
            ->assertSee('Premium')
            ->assertDontSee('Le champ template id est obligatoire.');

        $this->assertSame(8, ResumeTemplate::query()->count());

        $this->post(route('cvs.store'), ['title' => 'Sans modèle'])
            ->assertSessionHasErrors([
                'template_id' => 'Choisissez un modèle.',
            ]);
    }

    public function test_an_account_without_a_plan_receives_the_free_plan_and_can_create_a_resume(): void
    {
        $user = $this->makeUser();
        $user->subscription()->delete();
        $template = ResumeTemplate::query()->where('slug', 'moderne')->firstOrFail();

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

    public function test_the_create_page_preselects_the_preferred_template(): void
    {
        $user = $this->makeUser();
        $preferences = $user->profile->preferences;
        $preferences['default_template'] = 'classique';
        $user->profile->forceFill(['preferences' => $preferences])->save();
        $classique = ResumeTemplate::query()->where('slug', 'classique')->firstOrFail();

        $this->actingAs($user)->get(route('cvs.create'))
            ->assertOk()
            ->assertSee('name="template_id" value="'.$classique->id.'" checked', false);
    }

    public function test_the_editor_can_remember_the_selected_template(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);
        $classique = ResumeTemplate::query()->where('slug', 'classique')->firstOrFail();

        $this->actingAs($user)->postJson(route('cvs.prefer-template', $resume), [
            'template_id' => $classique->id,
        ])->assertOk()
            ->assertJsonPath('successful', true);

        $this->assertSame('classique', $user->profile->fresh()->preference('default_template'));
    }
}
