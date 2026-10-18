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
            ->assertSee('Choisissez un modèle, puis remplissez votre CV.')
            ->assertSee('CV classiques')
            ->assertSee('Gratuit')
            ->assertSee('15 000')
            ->assertDontSee('10 000 utilisateurs');

        $this->get(route('templates.index'))
            ->assertOk()
            ->assertSee('Moderne')
            ->assertSee('Élégant')
            ->assertSee('Technique')
            ->assertSee('Choisir ce modèle')
            ->assertSee('Aperçu détaillé')
            ->assertSee('data-gallery-filter="original"', false)
            ->assertSee('data-category="classique"', false)
            ->assertSee('miniature=1', false);
        $this->get(route('templates.example', 'moderne'))->assertOk()->assertSee('données fictives');
        $this->get(route('pricing'))->assertOk()->assertSee('Pro');
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
}
