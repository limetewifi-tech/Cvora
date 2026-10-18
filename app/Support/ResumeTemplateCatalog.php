<?php

namespace App\Support;

use App\Models\ResumeTemplate;

class ResumeTemplateCatalog
{
    public static function definitions(): array
    {
        $standard = ResumeContent::defaultOrder();
        $student = ['education', 'skills', 'languages', 'projects', 'experiences', 'certifications', 'interests', 'references'];
        $technical = ['skills', 'experiences', 'projects', 'education', 'certifications', 'languages', 'interests', 'references'];

        return [
            ['slug' => 'classique', 'name' => 'Classique', 'category' => 'classique', 'description' => 'Une colonne centrée, pour une lecture formelle.', 'audience' => 'Candidatures institutionnelles', 'is_premium' => false, 'sort_order' => 1, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'serif'],
            ['slug' => 'minimaliste', 'name' => 'Minimaliste', 'category' => 'design', 'description' => 'Beaucoup d\'air, le texte d\'abord.', 'audience' => 'Profils qui veulent une lecture directe', 'is_premium' => false, 'sort_order' => 2, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#A8A29E', 'font' => 'sans'],
            ['slug' => 'moderne', 'name' => 'Moderne', 'category' => 'moderne', 'description' => 'Bandeau coloré et sections lisibles rapidement.', 'audience' => 'Profils polyvalents', 'is_premium' => false, 'sort_order' => 3, 'order' => $standard, 'primary' => '#0F766E', 'accent' => '#1C1917', 'font' => 'sans'],
            ['slug' => 'technique', 'name' => 'Deux colonnes', 'category' => 'moderne', 'description' => 'Expériences à gauche, compétences à droite.', 'audience' => 'Parcours techniques et polyvalents', 'is_premium' => true, 'sort_order' => 4, 'order' => $technical, 'primary' => '#134E4A', 'accent' => '#0F766E', 'font' => 'sans'],
            ['slug' => 'experimente', 'name' => 'Colonne latérale', 'category' => 'classique', 'description' => 'Contact et compétences dans une colonne, le parcours dans l\'autre.', 'audience' => 'Profils confirmés', 'is_premium' => true, 'sort_order' => 5, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'sans'],
            ['slug' => 'international', 'name' => 'Élégant', 'category' => 'design', 'description' => 'Filets fins et titre espacé, sans bloc de couleur.', 'audience' => 'Présentations soignées', 'is_premium' => true, 'sort_order' => 6, 'order' => $standard, 'primary' => '#44403C', 'accent' => '#0F766E', 'font' => 'serif'],
            ['slug' => 'etudiant', 'name' => 'Créatif', 'category' => 'original', 'description' => 'Titre large et rubriques marquées, la formation reste lisible.', 'audience' => 'Premiers emplois et profils visuels', 'is_premium' => false, 'sort_order' => 7, 'order' => $student, 'primary' => '#9A3412', 'accent' => '#1C1917', 'font' => 'sans'],
            ['slug' => 'administratif', 'name' => 'ATS', 'category' => 'classique', 'description' => 'Une seule colonne, sans fond coloré, pour les logiciels de tri.', 'audience' => 'Candidatures filtrées automatiquement', 'is_premium' => true, 'sort_order' => 8, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#1C1917', 'font' => 'sans'],
        ];
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
}
