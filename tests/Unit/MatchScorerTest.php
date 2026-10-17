<?php

namespace Tests\Unit;

use App\Models\Resume;
use App\Services\Matching\MatchScorer;
use Tests\TestCase;

class MatchScorerTest extends TestCase
{
    public function test_the_score_counts_only_requirements_already_written_on_the_resume(): void
    {
        $resume = new Resume([
            'summary' => 'Analyste à Kinshasa.',
            'content' => [
                'skills' => [
                    ['name' => 'Excel'],
                ],
                'experiences' => [[
                    'title' => 'Analyste',
                    'company' => 'Banque exemple',
                    'description' => 'Suivi des dossiers clients.',
                ]],
            ],
        ]);

        $result = app(MatchScorer::class)->score($resume, "Excel\nPython\nSuivi des dossiers");

        $this->assertSame(67, $result['score']);
        $this->assertSame(['Excel', 'Suivi des dossiers'], $result['matched']);
        $this->assertSame(['Python'], $result['missing']);
        $this->assertSame('Analyste', $result['relevant_experiences'][0]['title']);
        $this->assertStringNotContainsString('Python', implode(' ', $result['matched']));
    }

    public function test_an_offer_without_a_requirement_has_no_score(): void
    {
        $resume = new Resume([
            'summary' => 'Analyste.',
            'content' => ['skills' => [], 'experiences' => []],
        ]);

        $result = app(MatchScorer::class)->score($resume, 'et');

        $this->assertNull($result['score']);
        $this->assertSame([], $result['matched']);
        $this->assertStringContainsString('Aucune exigence', $result['explanation']);
    }
}
