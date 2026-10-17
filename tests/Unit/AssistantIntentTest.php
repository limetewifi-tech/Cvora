<?php

namespace Tests\Unit;

use App\Support\AssistantIntent;
use Tests\TestCase;

class AssistantIntentTest extends TestCase
{
    public function test_it_routes_plain_requests_to_a_concrete_action(): void
    {
        $this->assertSame(AssistantIntent::IMPROVE, AssistantIntent::detect('Améliore mon CV pour un poste de développeur Laravel.'));
        $this->assertSame(AssistantIntent::SUMMARY, AssistantIntent::detect('Rends mon résumé plus professionnel.'));
        $this->assertSame(AssistantIntent::OFFER, AssistantIntent::detect('Adapte mon CV à cette offre d\'emploi.'));
        $this->assertSame(AssistantIntent::LETTER, AssistantIntent::detect('Crée une lettre de motivation à partir de mon CV.'));
        $this->assertSame(AssistantIntent::GAPS, AssistantIntent::detect('Identifie les points faibles de mon profil.'));
        $this->assertSame(AssistantIntent::PROOFREAD, AssistantIntent::detect('Corrige les fautes de ce CV.'));
    }
}
