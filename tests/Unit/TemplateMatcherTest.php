<?php

namespace Tests\Unit;

use App\Support\TemplateMatcher;
use Tests\TestCase;

class TemplateMatcherTest extends TestCase
{
    public function test_education_without_experience_suggests_the_student_template(): void
    {
        $suggestion = TemplateMatcher::suggest([
            'education' => [['degree' => 'Licence']],
            'experiences' => [],
        ], false);

        $this->assertSame('etudiant', $suggestion['slug']);
        $this->assertStringContainsString('pas une copie visuelle', $suggestion['reason']);
    }

    public function test_many_skills_suggest_the_technical_template_only_when_premium_is_allowed(): void
    {
        $content = [
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'SQL'],
                ['name' => 'Linux'],
                ['name' => 'Git'],
            ],
            'experiences' => [['title' => 'Développeur', 'company' => 'Exemple']],
        ];

        $this->assertSame('moderne', TemplateMatcher::suggest($content, false)['slug']);
        $this->assertSame('technique', TemplateMatcher::suggest($content, true)['slug']);
    }

    public function test_two_experiences_suggest_the_experienced_template_when_premium_is_allowed(): void
    {
        $content = [
            'experiences' => [
                ['title' => 'Analyste', 'company' => 'Banque'],
                ['title' => 'Chargé', 'company' => 'Bureau'],
            ],
        ];

        $this->assertSame('moderne', TemplateMatcher::suggest($content, false)['slug']);
        $this->assertSame('experimente', TemplateMatcher::suggest($content, true)['slug']);
    }

    public function test_a_preferred_free_template_is_kept_when_nothing_else_matches(): void
    {
        $suggestion = TemplateMatcher::suggest([], false, 'classique');

        $this->assertSame('classique', $suggestion['slug']);
        $this->assertStringContainsString('modèle retenu', $suggestion['reason']);
    }

    public function test_a_preferred_premium_template_is_ignored_on_the_free_plan(): void
    {
        $this->assertSame('moderne', TemplateMatcher::suggest([], false, 'technique')['slug']);
        $this->assertSame('technique', TemplateMatcher::suggest([], true, 'technique')['slug']);
    }
}
