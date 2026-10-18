<?php

namespace Tests\Feature;

use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class AssistantAskTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_the_assistant_page_offers_one_request_box(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->get(route('assistant.index', ['cv' => $resume->id]))
            ->assertOk()
            ->assertSee('Lancer')
            ->assertSee('Outils séparés')
            ->assertSee('value="'.$resume->id.'" selected', false);
    }

    public function test_finding_gaps_does_not_spend_a_credit_or_change_the_resume(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user, ['summary' => '']);

        $this->actingAs($user)->post(route('assistant.ask'), [
            'message' => 'Identifie les points faibles de mon profil.',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertRedirect()
            ->assertSessionHas('proposal.feature', 'gaps');

        $this->assertSame(5, (int) $user->fresh()->subscription->credits_remaining);
        $this->assertSame('', (string) $resume->fresh()->summary);
    }

    public function test_a_summary_request_returns_the_existing_text_until_the_user_applies_it(): void
    {
        $user = $this->makeUser();
        $resume = $this->makeResume($user);

        $this->actingAs($user)->post(route('assistant.ask'), [
            'message' => 'Rends mon résumé plus professionnel.',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertRedirect()
            ->assertSessionHas('proposal.successful', true)
            ->assertSessionHas('proposal.data.proposal', 'Analyste à Kinshasa.');

        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        $this->assertSame(4, (int) $user->fresh()->subscription->credits_remaining);

        $this->post(route('cvs.proposal', $resume), [
            'target' => 'summary',
            'value' => 'Analyste à Kinshasa.',
        ])->assertRedirect(route('cvs.edit', $resume));

        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
    }

    public function test_an_offer_request_without_text_does_not_spend_a_credit(): void
    {
        $user = $this->makeUser('pro');
        $resume = $this->makeResume($user);

        $this->actingAs($user)->post(route('assistant.ask'), [
            'message' => 'Adapte mon CV à cette offre d\'emploi.',
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(400, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_a_letter_request_uses_only_the_supplied_facts(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);

        $this->actingAs($user)->post(route('assistant.ask'), [
            'message' => 'Crée une lettre de motivation à partir de mon CV.',
            'resume_id' => $resume->id,
            'position' => 'Analyste',
            'company' => 'Banque exemple',
            'authorize_resume' => '1',
        ])->assertRedirect()
            ->assertSessionHas('proposal.feature', 'cover_letter');

        $body = (string) session('proposal.data.body');
        $this->assertStringContainsString('Analyste', $body);
        $this->assertStringContainsString('Banque exemple', $body);
        $this->assertSame('Analyste à Kinshasa.', $resume->fresh()->summary);
        $this->assertSame(0, $user->coverLetters()->count());
    }
}
