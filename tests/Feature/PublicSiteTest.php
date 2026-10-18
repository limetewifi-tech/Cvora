<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_public_pages_render_the_commercial_site(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Vos candidatures, plus simples avec CVora.')
            ->assertSee('Créez votre CV, améliorez votre parcours avec l\'IA et rédigez vos lettres en quelques minutes.', false)
            ->assertSee('Créer mon CV')
            ->assertSee('Améliorer mon CV')
            ->assertSee('Générer ma lettre')
            ->assertSee('Bienvenue. Commencez par l\'une de ces trois actions.', false)
            ->assertSee(route('cvs.create'), false)
            ->assertSee(route('imports.create'), false)
            ->assertSee(route('letters.create'), false)
            ->assertSee('Accueil')
            ->assertSee('Mes documents')
            ->assertSee('Mon compte')
            ->assertDontSee('10 000 utilisateurs')
            ->assertDontSee('Choisissez un modèle, puis remplissez votre CV.');

        $this->get(route('templates.index'))
            ->assertOk()
            ->assertSee('Moderne')
            ->assertSee('Élégant')
            ->assertSee('Technique')
            ->assertSee('Voir le modèle')
            ->assertSee('Utiliser ce modèle')
            ->assertSee('Créer un CV avec l\'IA', false)
            ->assertSee('Design by EDOS Services', false)
            ->assertSee('images/edos-services.png', false)
            ->assertSee('data-gallery-filter="original"', false)
            ->assertSee('data-category="classique"', false)
            ->assertSee('miniature=1', false);
        $this->get(route('templates.example', 'moderne'))->assertOk()->assertSee('données fictives');
        $this->get(route('pricing'))->assertOk()->assertSee('Pro')->assertSee('Gratuit')->assertSee('15 000');
        $this->get(route('resources'))->assertOk();
        $this->get(route('legal.privacy'))->assertOk();
        $this->get(route('legal.terms'))->assertOk();
        $this->get(route('legal.notice'))->assertOk();
        $this->get(route('contact'))->assertOk();
        $this->get('/fonctionnalites')->assertOk();
    }

    public function test_contact_form_stores_a_message_and_ignores_the_honeypot(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Amina Kabila',
            'email' => 'amina@exemple.cd',
            'subject' => 'Question sur les offres',
            'message' => 'Bonjour, je souhaite comprendre la différence entre Premium et Pro.',
        ])->assertSessionHas('status');

        $this->assertSame(1, ContactMessage::query()->count());

        $this->post(route('contact.store'), [
            'name' => 'Robot',
            'email' => 'robot@exemple.cd',
            'subject' => 'Spam',
            'message' => 'Message automatique.',
            'company_website' => 'https://spam.example',
        ])->assertSessionHas('status');

        $this->assertSame(1, ContactMessage::query()->count());
    }

    public function test_the_home_page_lists_the_signed_in_users_recent_documents(): void
    {
        $user = $this->makeUser();
        $this->makeResume($user, ['title' => 'CV de candidature']);
        $user->coverLetters()->create([
            'title' => 'Lettre pour la banque',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => 'Madame, Monsieur, je présente ma candidature.',
        ]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('CV de candidature')
            ->assertSee('Lettre pour la banque')
            ->assertDontSee('Bienvenue. Commencez par l\'une de ces trois actions.', false);
    }
}
