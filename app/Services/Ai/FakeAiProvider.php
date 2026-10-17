<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

/**
 * Double de test. Il reformule uniquement les faits reçus et ne doit jamais
 * être activé en production.
 */
class FakeAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function configured(): bool
    {
        return true;
    }

    public function complete(string $system, string $userPayload): array
    {
        preg_match('/FEATURE:([a-z_]+)/', $system, $feature);
        $data = $this->payload($userPayload);
        $name = $feature[1] ?? 'summary';

        return match ($name) {
            'experiences' => [
                'items' => array_map(fn ($item, $index) => [
                    'index' => $index,
                    'description' => trim((string) ($item['description'] ?? '')),
                ], $data['experiences'] ?? [], array_keys($data['experiences'] ?? [])),
                'missing_information' => [],
                'warnings' => ['Proposition de test : le texte d\'origine est conservé.'],
            ],
            'skills' => [
                'items' => array_map(fn ($item, $index) => [
                    'index' => $index,
                    'name' => trim((string) ($item['name'] ?? '')),
                ], $data['skills'] ?? [], array_keys($data['skills'] ?? [])),
                'missing_information' => [],
                'warnings' => [],
            ],
            'cover_letter' => $this->letter($data),
            'job_analysis' => $this->analysis($data),
            'refresh' => [
                'summary_proposal' => trim((string) ($data['summary'] ?? '')),
                'experiences' => [],
                'section_order' => $data['section_order'] ?? [],
                'missing_information' => blank($data['summary'] ?? null) ? ['Résumé professionnel'] : [],
                'warnings' => [],
            ],
            default => [
                'proposal' => trim((string) ($data['text'] ?? $data['summary'] ?? '')),
                'missing_information' => blank($data['text'] ?? $data['summary'] ?? null) ? ['Texte à reformuler'] : [],
                'warnings' => [],
            ],
        };
    }

    private function letter(array $data): array
    {
        $missing = [];

        foreach (['position' => 'Poste recherché', 'company' => 'Entreprise', 'summary' => 'Résumé ou expériences du candidat'] as $key => $label) {
            if (blank($data[$key] ?? null)) {
                $missing[] = $label;
            }
        }

        if ($missing) {
            return [
                'body' => '',
                'missing_information' => $missing,
                'warnings' => ['La lettre n\'a pas été rédigée tant que les informations manquantes ne sont pas fournies.'],
            ];
        }

        $body = "Madame, Monsieur,\n\nJe vous adresse ma candidature au poste de {$data['position']} au sein de {$data['company']}."
            ."\n\n{$data['summary']}\n\nJe reste disponible pour un échange.\n\nCordialement";

        return ['body' => $body, 'missing_information' => [], 'warnings' => []];
    }

    private function analysis(array $data): array
    {
        $required = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', (string) ($data['offer'] ?? '')) ?: [])));
        $known = array_values(array_filter(array_map(fn ($skill) => trim((string) ($skill['name'] ?? '')), $data['skills'] ?? [])));
        $matched = array_values(array_intersect($required, $known));
        $missingSkills = array_values(array_diff($required, $known));

        return [
            'required_skills' => $required,
            'matched_skills' => $matched,
            'missing_skills' => $missingSkills,
            'suggestions' => $missingSkills ? ['Le CV ne mentionne pas toutes les compétences citées dans l\'offre. Ne les ajoutez que si vous les possédez.'] : [],
            'summary_proposal' => trim((string) ($data['summary'] ?? '')),
            'missing_information' => blank($data['offer'] ?? null) ? ['Description de l\'offre'] : [],
            'warnings' => [],
        ];
    }

    private function payload(string $userPayload): array
    {
        if (! preg_match('/<donnees>\s*(.*?)\s*<\/donnees>/s', $userPayload, $matches)) {
            throw new AiException('Charge utile illisible.');
        }

        $decoded = json_decode($matches[1], true);

        if (! is_array($decoded)) {
            throw new AiException('Charge utile illisible.');
        }

        return $decoded;
    }
}
