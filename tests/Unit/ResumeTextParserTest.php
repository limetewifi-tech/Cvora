<?php

namespace Tests\Unit;

use App\Services\Resume\ResumeTextParser;
use Tests\TestCase;

class ResumeTextParserTest extends TestCase
{
    public function test_it_detects_french_sections_without_inventing_facts(): void
    {
        $text = implode("\n", [
            'Amina Kabila',
            'Analyste financière',
            'amina.kabila@exemple.cd',
            '+243810000000',
            'Kinshasa',
            'Résumé',
            'Analyste à Kinshasa.',
            'Expériences',
            'Analyste — Banque exemple, Kinshasa',
            '2022 — 2024',
            'Suivi des dossiers clients.',
            'Formation',
            'Licence — Université de Kinshasa',
            '2018 — 2021',
            'Compétences',
            'Excel, Analyse',
            'Langues',
            'Français — Courant',
        ]);

        $parsed = (new ResumeTextParser)->parse($text);

        $this->assertSame('Amina', $parsed['personal']['first_name']);
        $this->assertSame('Kabila', $parsed['personal']['last_name']);
        $this->assertSame('amina.kabila@exemple.cd', $parsed['personal']['email']);
        $this->assertSame('Kinshasa', $parsed['personal']['city']);
        $this->assertSame('Analyste à Kinshasa.', $parsed['summary']);
        $this->assertSame('Analyste', $parsed['content']['experiences'][0]['title']);
        $this->assertSame('Banque exemple', $parsed['content']['experiences'][0]['company']);
        $this->assertSame('Université de Kinshasa', $parsed['content']['education'][0]['school']);
        $this->assertSame('Excel', $parsed['content']['skills'][0]['name']);
        $this->assertSame('Français', $parsed['content']['languages'][0]['name']);
        $this->assertSame('Courant', $parsed['content']['languages'][0]['level']);
        $this->assertArrayNotHasKey('invented', $parsed);
    }
}
