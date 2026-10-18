<?php

namespace App\Support;

/**
 * Choisit l'un des modèles CVora déjà livrés, d'après les sections détectées.
 * Ce n'est pas une copie visuelle du fichier importé.
 */
class TemplateMatcher
{
    private const FREE = ['moderne', 'classique', 'minimaliste', 'etudiant'];

    private const PREMIUM = ['experimente', 'technique', 'administratif', 'international'];

    /**
     * @param  array<string, mixed>  $content
     * @return array{slug: string, reason: string}
     */
    public static function suggest(array $content, bool $premiumAllowed, ?string $preferredSlug = null): array
    {
        $experiences = self::filled($content['experiences'] ?? [], ['title', 'company', 'description']);
        $education = self::filled($content['education'] ?? [], ['degree', 'school']);
        $skills = self::filled($content['skills'] ?? [], ['name']);
        $note = ' Ce choix est le modèle CVora le plus proche du contenu détecté. Ce n\'est pas une copie visuelle du fichier importé.';

        if ($education > 0 && $experiences === 0) {
            return [
                'slug' => 'etudiant',
                'reason' => 'La formation est présente et aucune expérience n\'est détectée. Le modèle Bleu professionnel met la formation en avant.'.$note,
            ];
        }

        if ($skills >= 4 && $premiumAllowed) {
            return [
                'slug' => 'technique',
                'reason' => 'Plusieurs compétences sont détectées. Le modèle Technique les place à part.'.$note,
            ];
        }

        if ($experiences >= 2 && $premiumAllowed) {
            return [
                'slug' => 'experimente',
                'reason' => 'Au moins deux expériences sont détectées. Le modèle Corporate convient à un parcours déjà long.'.$note,
            ];
        }

        $preferred = self::allowed($preferredSlug, $premiumAllowed);

        if ($preferred !== null && $preferred !== 'moderne') {
            return [
                'slug' => $preferred,
                'reason' => 'Votre modèle retenu est proposé.'.$note,
            ];
        }

        return [
            'slug' => 'moderne',
            'reason' => 'Le modèle Moderne est proposé.'.$note,
        ];
    }

    public static function allowed(?string $slug, bool $premiumAllowed): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        if (in_array($slug, self::FREE, true)) {
            return $slug;
        }

        if ($premiumAllowed && in_array($slug, self::PREMIUM, true)) {
            return $slug;
        }

        return null;
    }

    /**
     * @param  array<int, string>  $keys
     */
    private static function filled(mixed $rows, array $keys): int
    {
        if (! is_array($rows)) {
            return 0;
        }

        $count = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($keys as $key) {
                if (filled($row[$key] ?? null)) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }
}
