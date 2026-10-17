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
            'coach' => $this->coach($data),
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

    private function coach(array $data): array
    {
        $resume = is_array($data['resume'] ?? null) ? $data['resume'] : [];
        $message = trim((string) ($data['message'] ?? ''));
        $summary = trim((string) ($resume['summary'] ?? ''));
        $missing = [];

        if (blank($resume['headline'] ?? null)) {
            $missing[] = 'Titre visé';
        }

        if ($summary === '') {
            $missing[] = 'Résumé professionnel';
        }

        if (! $this->hasFact($resume['experiences'] ?? [], ['title', 'description'])) {
            $missing[] = 'Expérience professionnelle';
        }

        if (! $this->hasFact($resume['education'] ?? [], ['degree', 'school'])) {
            $missing[] = 'Formation';
        }

        if (! $this->hasFact($resume['skills'] ?? [], ['name'])) {
            $missing[] = 'Compétence';
        }

        $suggestions = [];
        $askedForSummary = preg_match('/r[eé]sum[eé]|formulation/iu', $message) === 1;

        if ($summary !== '' && $askedForSummary) {
            $suggestions[] = [
                'target' => 'summary',
                'index' => null,
                'text' => $summary,
                'label' => 'Formulation à partir du résumé saisi',
            ];
        }

        $reply = $missing === []
            ? 'Les sections principales sont présentes. Je peux reformuler un texte déjà saisi, sans ajouter de fait.'
            : 'Il manque encore : '.implode(', ', $missing).'. Je ne complète pas ces éléments à votre place.';

        return [
            'reply' => $reply,
            'suggestions' => $suggestions,
            'missing_information' => $missing,
            'warnings' => ['Aucun fait nouveau n\'a été ajouté.'],
        ];
    }

    private function hasFact(mixed $rows, array $keys): bool
    {
        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($keys as $key) {
                if (filled($row[$key] ?? null)) {
                    return true;
                }
            }
        }

        return false;
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
