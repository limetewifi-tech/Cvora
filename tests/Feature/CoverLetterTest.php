<?php

namespace Tests\Feature;

use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class CoverLetterTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_a_letter_can_be_generated_from_real_profile_facts_and_saved(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);

        $response = $this->actingAs($user)->postJson(route('assistant.letter'), [
            'position' => 'Analyste',
            'company' => 'Banque exemple',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
            'tone' => 'professionnel',
            'length' => 'standard',
        ]);

        $response->assertOk();
        $body = $response->json('data.body');
        $this->assertStringContainsString('Analyste', $body);
        $this->assertStringContainsString('Banque exemple', $body);
        $this->assertStringContainsString('Analyste à Kinshasa.', $body);
        $this->assertSame($resume->summary, $resume->fresh()->summary);

        $this->post(route('letters.store'), [
            'title' => 'Lettre Banque exemple',
            'company' => 'Banque exemple',
            'position' => 'Analyste',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => $body,
            'resume_id' => $resume->id,
        ])->assertRedirect();

        $letter = $user->coverLetters()->first();
        $this->assertNotNull($letter);
        $this->assertSame($resume->id, $letter->resume_id);

        $this->put(route('letters.update', $letter), [
            'title' => 'Lettre révisée',
            'company' => 'Banque exemple',
            'position' => 'Analyste',
            'tone' => 'direct',
            'length' => 'courte',
            'body' => $body,
            'lock_version' => $letter->lock_version,
        ])->assertRedirect();

        $this->assertSame('direct', $letter->fresh()->tone);
    }

    public function test_a_saved_letter_prints_on_a4_without_changing_the_text(): void
    {
        $user = $this->makeUser('premium');
        $body = 'Madame, Monsieur, je présente ma candidature pour le poste indiqué.';
        $letter = $user->coverLetters()->create([
            'title' => 'Lettre A4',
            'company' => 'Banque exemple',
            'position' => 'Analyste',
            'tone' => 'professionnel',
            'length' => 'standard',
            'layout' => 'bandeau',
            'body' => $body,
        ]);

        $this->actingAs($user)->get(route('letters.index'))
            ->assertOk()
            ->assertSee('Classique')
            ->assertSee('Bandeau')
            ->assertSee('Élégant');

        $this->get(route('letters.sample', 'classique'))
            ->assertOk()
            ->assertSee('size: A4 portrait', false)
            ->assertSee('210mm', false)
            ->assertSee('Le texte de votre lettre apparaît ici.');

        $this->get(route('letters.preview', $letter).'?cadre=1')
            ->assertOk()
            ->assertSee('layout-bandeau', false)
            ->assertSee($body)
            ->assertSee('Amina')
            ->assertSee('Banque exemple')
            ->assertSee('Objet : candidature au poste de Analyste');

        $pdf = app(\App\Services\Documents\PdfExporter::class)->letter($letter)->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(1000, strlen($pdf));
        $this->assertSame($body, $letter->fresh()->body);
    }

    public function test_free_plan_cannot_create_a_cover_letter(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('letters.store'), [
            'title' => 'Lettre',
            'tone' => 'classique',
            'length' => 'courte',
            'body' => 'Texte.',
        ])->assertSessionHas('error');

        $this->assertSame(0, $user->coverLetters()->count());
    }

    public function test_manual_letter_history_requires_the_pro_plan(): void
    {
        $premium = $this->makeUser('premium');
        $letter = $premium->coverLetters()->create([
            'title' => 'Lettre',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => 'Version originale.',
        ]);

        $this->actingAs($premium)->post(route('letters.versions.store', $letter))
            ->assertSessionHas('error');

        $pro = $this->makeUser('pro');
        $proLetter = $pro->coverLetters()->create([
            'title' => 'Lettre pro',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => 'Version originale.',
        ]);

        $this->actingAs($pro)->post(route('letters.versions.store', $proLetter), ['note' => 'Avant envoi'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cover_letter_versions', [
            'cover_letter_id' => $proLetter->id,
            'kind' => 'manual',
        ]);
    }
}
