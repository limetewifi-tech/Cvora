<?php

namespace App\Support;

class LetterLayouts
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $all = null;

    public static function categories(): array
    {
        return [
            'emploi' => 'Emploi',
            'stage' => 'Stage',
            'alternance' => 'Alternance',
            'spontanee' => 'Candidature spontanée',
            'ong' => 'ONG',
            'administration' => 'Administration',
            'informatique' => 'Informatique',
            'commerce' => 'Commerce',
            'sante' => 'Santé',
            'enseignement' => 'Enseignement',
        ];
    }

    public static function families(): array
    {
        return [
            'classique' => 'Expéditeur à gauche, destinataire à droite, objet puis signature.',
            'moderne' => 'Bandeau avec le nom, puis les deux adresses sur une ligne.',
            'bandeau' => 'Titre coloré en tête, le poste visé juste en dessous.',
            'elegant' => 'Nom en grand, filet fin, destinataire et texte aérés.',
            'colonne' => 'Coordonnées dans une colonne colorée, lettre à droite.',
            'centre' => 'Nom et objet centrés, adresses ensuite.',
            'filet' => 'Nom, filet épais, puis les adresses l\'une sous l\'autre.',
            'encadre' => 'Bloc expéditeur encadré, destinataire en dessous.',
            'compact' => 'En-tête serré sur deux colonnes et texte plus haut.',
            'bande' => 'Bande du nom seule, objet sur une ligne d\'accent.',
        ];
    }

    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }

        $rows = [
            'classique' => self::row('Classique', 'classique', 'emploi', '#1C1917', 'Expéditeur à gauche, destinataire à droite, objet puis signature.'),
            'moderne' => self::row('Moderne', 'moderne', 'emploi', '#0F766E', 'Bandeau avec le nom, puis les deux adresses sur une ligne.'),
            'bandeau' => self::row('Bandeau', 'bandeau', 'emploi', '#134E4A', 'Titre coloré en tête, le poste visé juste en dessous.'),
            'elegant' => self::row('Élégant', 'elegant', 'emploi', '#1C1917', 'Nom en grand, filet fin, destinataire et texte aérés.'),
        ];
        $categoryIndex = 0;

        foreach (self::categories() as $category => $label) {
            $familyIndex = 0;

            foreach (self::families() as $family => $description) {
                $familyIndex++;

                if ($category === 'emploi' && in_array($family, ['classique', 'moderne', 'bandeau', 'elegant'], true)) {
                    continue;
                }

                $hue = ($categoryIndex * 28 + $familyIndex * 19) % 360;
                $slug = $category.'-'.$family;
                $rows[$slug] = self::row(
                    self::familyLabel($family).' · '.$label,
                    $family,
                    $category,
                    self::hex($hue),
                    $description.' Pour une lettre de '.$label.'.',
                );
            }

            $categoryIndex++;
        }

        return self::$all = $rows;
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function has(string $layout): bool
    {
        return isset(self::all()[$layout]);
    }

    /**
     * @return array{name: string, description: string, family: string, category: string, accent: string}
     */
    private static function row(string $name, string $family, string $category, string $accent, string $description): array
    {
        return [
            'name' => $name,
            'description' => $description,
            'family' => $family,
            'category' => $category,
            'accent' => $accent,
        ];
    }

    private static function familyLabel(string $family): string
    {
        return [
            'classique' => 'Classique',
            'moderne' => 'Moderne',
            'bandeau' => 'Bandeau',
            'elegant' => 'Élégant',
            'colonne' => 'Colonne',
            'centre' => 'Centré',
            'filet' => 'Filet',
            'encadre' => 'Encadré',
            'compact' => 'Compact',
            'bande' => 'Bande',
        ][$family];
    }

    private static function hex(int $hue): string
    {
        $s = 0.45;
        $l = 0.28;
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($hue / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $hue < 60 => [$c, $x, 0],
            $hue < 120 => [$x, $c, 0],
            $hue < 180 => [0, $c, $x],
            $hue < 240 => [0, $x, $c],
            $hue < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf('#%02X%02X%02X', (int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255));
    }
}
