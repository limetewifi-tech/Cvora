<?php

namespace App\Support;

use App\Models\ResumeTemplate;

class ResumeTemplateCatalog
{
    /** @var array<int, array<string, mixed>>|null */
    private static ?array $definitions = null;

    public static function sectors(): array
    {
        return [
            'informatique' => 'Informatique, développement et réseaux',
            'administration' => 'Administration et secrétariat',
            'comptabilite' => 'Comptabilité, finance et gestion',
            'commerce' => 'Commerce, vente et marketing',
            'ingenierie' => 'Ingénierie et métiers techniques',
            'sante' => 'Santé et professions médicales',
            'enseignement' => 'Enseignement et formation',
            'etudiants' => 'Étudiants, stages et premiers emplois',
            'management' => 'Management, direction et ressources humaines',
            'logistique' => 'Logistique, transport et chaîne d\'approvisionnement',
            'communication' => 'Communication, design et métiers créatifs',
            'hotellerie' => 'Hôtellerie, restauration et tourisme',
            'construction' => 'Construction, bâtiment et travaux publics',
            'banque' => 'Banque, assurance et services financiers',
            'ong' => 'ONG, développement et projets',
        ];
    }

    public static function styles(): array
    {
        $standard = ResumeContent::defaultOrder();
        $student = ['education', 'skills', 'languages', 'projects', 'experiences', 'certifications', 'interests', 'references'];
        $technical = ['skills', 'experiences', 'projects', 'education', 'certifications', 'languages', 'interests', 'references'];

        return [
            'classic' => ['label' => 'Classique', 'layout' => 'classic', 'category' => 'classique', 'font' => 'serif', 'premium' => false, 'order' => $standard, 'candidature' => 'emploi'],
            'minimal' => ['label' => 'Minimaliste', 'layout' => 'minimal', 'category' => 'design', 'font' => 'serif', 'premium' => false, 'order' => $standard, 'candidature' => 'emploi'],
            'modern' => ['label' => 'Moderne', 'layout' => 'modern', 'category' => 'moderne', 'font' => 'sans', 'premium' => false, 'order' => $standard, 'candidature' => 'emploi'],
            'technical' => ['label' => 'Technique', 'layout' => 'technical', 'category' => 'moderne', 'font' => 'sans', 'premium' => true, 'order' => $technical, 'candidature' => 'emploi'],
            'corporate' => ['label' => 'Corporate', 'layout' => 'corporate', 'category' => 'original', 'font' => 'sans', 'premium' => true, 'order' => $standard, 'candidature' => 'emploi'],
            'elegant' => ['label' => 'Élégant', 'layout' => 'elegant', 'category' => 'design', 'font' => 'serif', 'premium' => true, 'order' => $standard, 'candidature' => 'emploi'],
            'blue' => ['label' => 'Bleu professionnel', 'layout' => 'blue', 'category' => 'original', 'font' => 'sans', 'premium' => false, 'order' => $student, 'candidature' => 'stage'],
            'executive' => ['label' => 'Exécutif', 'layout' => 'executive', 'category' => 'classique', 'font' => 'serif', 'premium' => true, 'order' => $standard, 'candidature' => 'emploi'],
            'rail' => ['label' => 'Colonne droite', 'layout' => 'rail', 'category' => 'original', 'font' => 'sans', 'premium' => true, 'order' => $standard, 'candidature' => 'emploi'],
            'banner' => ['label' => 'Bandeau', 'layout' => 'banner', 'category' => 'moderne', 'font' => 'sans', 'premium' => false, 'order' => $standard, 'candidature' => 'reconversion'],
        ];
    }

    public static function definitions(): array
    {
        return self::$definitions ??= array_merge(self::legacy(), self::generated());
    }

    public static function find(string $slug): ?array
    {
        foreach (self::definitions() as $definition) {
            if ($definition['slug'] === $slug) {
                return $definition;
            }
        }

        return null;
    }

    public static function layoutFor(string $slug): string
    {
        return self::find($slug)['layout'] ?? 'modern';
    }

    public static function viewFor(string $slug, string $layout): string
    {
        $own = 'resumes.templates.'.$slug;

        if (view()->exists($own)) {
            return $own;
        }

        return match ($layout) {
            'classic' => 'resumes.templates.classique',
            'minimal' => 'resumes.templates.minimaliste',
            'modern' => 'resumes.templates.moderne',
            'technical' => 'resumes.templates.technique',
            'corporate' => 'resumes.templates.experimente',
            'elegant' => 'resumes.templates.international',
            'blue' => 'resumes.templates.etudiant',
            'executive' => 'resumes.templates.administratif',
            'rail' => 'resumes.templates.layouts.rail',
            'banner' => 'resumes.templates.layouts.banner',
            default => 'resumes.templates.moderne',
        };
    }

    public static function installMissing(): void
    {
        $present = ResumeTemplate::query()->pluck('slug')->all();

        foreach (self::definitions() as $template) {
            if (in_array($template['slug'], $present, true)) {
                continue;
            }

            ResumeTemplate::query()->create(self::attributes($template));
        }
    }

    public static function sync(): void
    {
        foreach (self::definitions() as $template) {
            ResumeTemplate::query()->updateOrCreate(
                ['slug' => $template['slug']],
                self::attributes($template),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private static function attributes(array $template): array
    {
        return [
            'slug' => $template['slug'],
            'name' => $template['name'],
            'description' => $template['description'],
            'audience' => $template['audience'],
            'is_premium' => $template['is_premium'],
            'is_active' => true,
            'sort_order' => $template['sort_order'],
            'default_section_order' => $template['order'],
            'default_theme' => [
                'primary' => $template['primary'],
                'accent' => $template['accent'],
                'font' => $template['font'],
                'size' => 'm',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function legacy(): array
    {
        $styles = self::styles();

        return [
            self::legacyRow('classique', 'Classique', 'classique', 'Identité en tête, photo carrée et séparateurs fins sur une colonne.', 'Candidatures institutionnelles', false, 1, 'administration', 'classic', '#1C1917', '#0F766E', 'serif', $styles['classic']['order']),
            self::legacyRow('minimaliste', 'Minimaliste', 'design', 'Typographie aérée, photo ronde discrète et beaucoup de blanc.', 'Profils qui veulent une lecture directe', false, 2, 'communication', 'minimal', '#1C1917', '#A8A29E', 'serif', $styles['minimal']['order']),
            self::legacyRow('moderne', 'Moderne', 'moderne', 'Photo, métier, barre de couleur, puis les rubriques les unes sous les autres.', 'Profils polyvalents', false, 3, 'informatique', 'modern', '#0F766E', '#1C1917', 'sans', $styles['modern']['order']),
            self::legacyRow('technique', 'Technique', 'moderne', 'Compétences, projets et certifications à gauche, expériences détaillées à droite.', 'Parcours techniques et polyvalents', true, 4, 'ingenierie', 'technical', '#134E4A', '#0F766E', 'sans', $styles['technical']['order']),
            self::legacyRow('experimente', 'Corporate', 'classique', 'Colonne sombre, photo rectangulaire, coordonnées et compétences.', 'Profils confirmés', true, 5, 'management', 'corporate', '#1C1917', '#0F766E', 'sans', $styles['corporate']['order']),
            self::legacyRow('international', 'Élégant', 'design', 'Photo ronde, bandeau clair et deux colonnes.', 'Présentations soignées', true, 6, 'communication', 'elegant', '#1C1917', '#0F766E', 'serif', $styles['elegant']['order']),
            self::legacyRow('etudiant', 'Bleu professionnel', 'original', 'Colonne bleue, photo en haut et expériences dans la zone principale.', 'Premiers emplois et profils visuels', false, 7, 'etudiants', 'blue', '#1E3A8A', '#1E40AF', 'sans', $styles['blue']['order']),
            self::legacyRow('administratif', 'Exécutif', 'classique', 'Bandeau sombre, photo ronde et parcours en deux colonnes pour un poste de responsabilité.', 'Cadres et responsables', true, 8, 'administration', 'executive', '#1C1917', '#B45309', 'serif', $styles['executive']['order']),
        ];
    }

    /**
     * @param  array<int, string>  $order
     * @return array<string, mixed>
     */
    private static function legacyRow(string $slug, string $name, string $category, string $description, string $audience, bool $premium, int $sort, string $sector, string $style, string $primary, string $accent, string $font, array $order): array
    {
        return [
            'slug' => $slug,
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'audience' => $audience,
            'is_premium' => $premium,
            'sort_order' => $sort,
            'sector' => $sector,
            'style' => $style,
            'layout' => self::styles()[$style]['layout'],
            'candidature' => self::styles()[$style]['candidature'],
            'order' => $order,
            'primary' => $primary,
            'accent' => $accent,
            'font' => $font,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function generated(): array
    {
        $reserved = [
            'informatique.modern' => true,
            'administration.classic' => true,
            'administration.executive' => true,
            'ingenierie.technical' => true,
            'management.corporate' => true,
            'communication.minimal' => true,
            'communication.elegant' => true,
            'etudiants.blue' => true,
        ];
        $rows = [];
        $sectorIndex = 0;

        foreach (self::sectors() as $sector => $label) {
            $styleIndex = 0;

            foreach (self::styles() as $style => $meta) {
                $styleIndex++;

                if (isset($reserved[$sector.'.'.$style])) {
                    continue;
                }

                $hue = ($sectorIndex * 23 + $styleIndex * 17) % 360;
                $rows[] = [
                    'slug' => $sector.'-'.$style,
                    'name' => $meta['label'].' · '.self::shortSector($sector),
                    'category' => $meta['category'],
                    'description' => self::styleDescription($style, $label),
                    'audience' => $label,
                    'is_premium' => $meta['premium'],
                    'sort_order' => 100 + ($sectorIndex * 10) + $styleIndex,
                    'sector' => $sector,
                    'style' => $style,
                    'layout' => $meta['layout'],
                    'candidature' => $sector === 'etudiants' ? 'stage' : $meta['candidature'],
                    'order' => $meta['order'],
                    'primary' => self::hsl($hue, 42, 24),
                    'accent' => self::hsl(($hue + 28) % 360, 55, 36),
                    'font' => $meta['font'],
                ];
            }

            $sectorIndex++;
        }

        return $rows;
    }

    private static function shortSector(string $sector): string
    {
        return [
            'informatique' => 'Informatique',
            'administration' => 'Administration',
            'comptabilite' => 'Comptabilité',
            'commerce' => 'Commerce',
            'ingenierie' => 'Ingénierie',
            'sante' => 'Santé',
            'enseignement' => 'Enseignement',
            'etudiants' => 'Étudiants',
            'management' => 'Management',
            'logistique' => 'Logistique',
            'communication' => 'Communication',
            'hotellerie' => 'Hôtellerie',
            'construction' => 'Bâtiment',
            'banque' => 'Banque',
            'ong' => 'ONG',
        ][$sector];
    }

    private static function styleDescription(string $style, string $sectorLabel): string
    {
        $layout = [
            'classic' => 'Une colonne, photo carrée et titres centrés.',
            'minimal' => 'Typographie aérée, photo ronde et filets fins.',
            'modern' => 'Photo à gauche, barre de couleur et rubriques soulignées.',
            'technical' => 'Compétences dans une colonne grise, parcours à droite.',
            'corporate' => 'Colonne sombre à gauche, photo portrait et coordonnées.',
            'elegant' => 'Bandeau clair, photo ronde et deux colonnes.',
            'blue' => 'Colonne colorée, pastilles et expériences bordées.',
            'executive' => 'Bandeau sombre, filet d\'accent et deux colonnes.',
            'rail' => 'Parcours à gauche et colonne de contact à droite.',
            'banner' => 'Grand bandeau coloré, puis le parcours sur une colonne.',
        ][$style];

        return $layout.' Pensé pour : '.$sectorLabel.'.';
    }

    private static function hsl(int $hue, int $saturation, int $lightness): string
    {
        $s = $saturation / 100;
        $l = $lightness / 100;
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
