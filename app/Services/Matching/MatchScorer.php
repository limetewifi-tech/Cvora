<?php

namespace App\Services\Matching;

use App\Models\Resume;
use Illuminate\Support\Str;

/**
 * Comparaison locale, sans appel à un fournisseur.
 *
 * Une exigence est un segment de l'offre séparé par une virgule, un
 * point-virgule, un retour à la ligne ou une puce, long de 2 à 80 caractères.
 * Elle est présente si son texte normalisé figure dans le résumé, les
 * compétences ou les expériences du CV. Une compétence déjà écrite dans le CV
 * compte aussi comme exigence lorsque son intitulé apparaît dans l'offre.
 * Score = arrondi(100 × exigences présentes / exigences identifiées).
 * Aucune compétence absente du CV n'est ajoutée.
 */
class MatchScorer
{
    public const METHOD = 'Comparaison locale : chaque exigence explicite de l\'offre, ou chaque compétence du CV citée dans l\'offre, est cherchée dans le résumé, les compétences et les expériences déjà enregistrés. Le score est le pourcentage d\'exigences présentes. Il n\'est pas produit par un fournisseur d\'IA.';

    /**
     * @return array{
     *     method: string,
     *     score: int|null,
     *     matched: list<string>,
     *     missing: list<string>,
     *     relevant_experiences: list<array{index: int, title: string, company: string, reason: string}>,
     *     explanation: string
     * }
     */
    public function score(Resume $resume, string $offerText): array
    {
        $skills = $this->skillNames($resume);
        $blob = $this->blob($resume, $skills);
        $requirements = $this->requirements($offerText, $skills);
        $matched = [];
        $missing = [];

        foreach ($requirements as $requirement) {
            if ($this->contains($blob, $requirement['normalized'])) {
                $matched[] = $requirement['label'];
            } else {
                $missing[] = $requirement['label'];
            }
        }

        $total = count($matched) + count($missing);
        $score = $total === 0 ? null : (int) round(100 * count($matched) / $total);
        $relevant = $this->experiences($resume, $matched);

        return [
            'method' => self::METHOD,
            'score' => $score,
            'matched' => $matched,
            'missing' => $missing,
            'relevant_experiences' => $relevant,
            'explanation' => $this->explanation($score, count($matched), count($missing), count($relevant)),
        ];
    }

    /**
     * @param  list<string>  $skills
     * @return list<array{label: string, normalized: string}>
     */
    private function requirements(string $offerText, array $skills): array
    {
        $items = [];
        $segments = preg_split('/[,;\n•·]+/u', $offerText) ?: [];

        foreach ($segments as $segment) {
            $label = trim(preg_replace('/\s+/u', ' ', $segment) ?? '');
            $normalized = $this->normalize($label);

            if (mb_strlen($label) < 2 || mb_strlen($label) > 80 || $normalized === '' || $this->isStopWord($normalized)) {
                continue;
            }

            $items[$normalized] = ['label' => $label, 'normalized' => $normalized];
        }

        $offer = $this->normalize($offerText);

        foreach ($skills as $skill) {
            $normalized = $this->normalize($skill);

            if ($normalized === '' || isset($items[$normalized]) || ! $this->contains($offer, $normalized)) {
                continue;
            }

            $items[$normalized] = ['label' => $skill, 'normalized' => $normalized];
        }

        return array_values($items);
    }

    /**
     * @return list<string>
     */
    private function skillNames(Resume $resume): array
    {
        $names = [];

        foreach ($resume->content['skills'] ?? [] as $skill) {
            $name = trim((string) ($skill['name'] ?? ''));

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $skills
     */
    private function blob(Resume $resume, array $skills): string
    {
        $parts = [(string) $resume->summary, ...$skills];

        foreach ($resume->content['experiences'] ?? [] as $experience) {
            $parts[] = (string) ($experience['title'] ?? '');
            $parts[] = (string) ($experience['description'] ?? '');
        }

        return $this->normalize(implode(' ', $parts));
    }

    /**
     * @param  list<string>  $matched
     * @return list<array{index: int, title: string, company: string, reason: string}>
     */
    private function experiences(Resume $resume, array $matched): array
    {
        $relevant = [];

        foreach ($resume->content['experiences'] ?? [] as $index => $experience) {
            $text = $this->normalize(((string) ($experience['title'] ?? '')).' '.((string) ($experience['description'] ?? '')));
            $reasons = [];

            foreach ($matched as $requirement) {
                if ($this->contains($text, $this->normalize($requirement))) {
                    $reasons[] = $requirement;
                }
            }

            if ($reasons === []) {
                continue;
            }

            $relevant[] = [
                'index' => (int) $index,
                'title' => trim((string) ($experience['title'] ?? 'Expérience')),
                'company' => trim((string) ($experience['company'] ?? '')),
                'reason' => 'Contient : '.implode(', ', $reasons).'.',
            ];
        }

        return $relevant;
    }

    private function explanation(?int $score, int $matched, int $missing, int $experiences): string
    {
        if ($score === null) {
            return 'Aucune exigence identifiable. Écrivez les compétences demandées, une par ligne ou séparées par des virgules. Aucun score n\'a été calculé.';
        }

        return $matched.' exigence(s) déjà écrite(s) dans le CV, '.$missing.' absente(s), '.$experiences.' expérience(s) en lien. Le score '.$score.' est le pourcentage d\'exigences présentes. Il ne crée aucune compétence.';
    }

    private function contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && str_contains($haystack, $needle);
    }

    private function normalize(string $value): string
    {
        $value = Str::ascii(mb_strtolower(trim($value)));
        $value = preg_replace('/[^a-z0-9+#. ]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function isStopWord(string $value): bool
    {
        return in_array($value, ['de', 'du', 'des', 'et', 'ou', 'le', 'la', 'les', 'un', 'une', 'en', 'au', 'aux', 'pour', 'par', 'sur', 'avec', 'dans', 'the', 'and', 'of'], true);
    }
}
