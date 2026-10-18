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
            ['slug' => 'classique', 'name' => 'Classique', 'category' => 'classique', 'description' => 'Identité en tête, photo carrée et séparateurs fins sur une colonne.', 'audience' => 'Candidatures institutionnelles', 'is_premium' => false, 'sort_order' => 1, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'serif'],
            ['slug' => 'minimaliste', 'name' => 'Minimaliste', 'category' => 'design', 'description' => 'Typographie aérée, photo ronde discrète et beaucoup de blanc.', 'audience' => 'Profils qui veulent une lecture directe', 'is_premium' => false, 'sort_order' => 2, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#A8A29E', 'font' => 'serif'],
            ['slug' => 'moderne', 'name' => 'Moderne', 'category' => 'moderne', 'description' => 'Photo, métier, barre de couleur, puis les rubriques les unes sous les autres.', 'audience' => 'Profils polyvalents', 'is_premium' => false, 'sort_order' => 3, 'order' => $standard, 'primary' => '#0F766E', 'accent' => '#1C1917', 'font' => 'sans'],
            ['slug' => 'technique', 'name' => 'Technique', 'category' => 'moderne', 'description' => 'Compétences, projets et certifications à gauche, expériences détaillées à droite.', 'audience' => 'Parcours techniques et polyvalents', 'is_premium' => true, 'sort_order' => 4, 'order' => $technical, 'primary' => '#134E4A', 'accent' => '#0F766E', 'font' => 'sans'],
            ['slug' => 'experimente', 'name' => 'Corporate', 'category' => 'classique', 'description' => 'Colonne sombre, photo rectangulaire, coordonnées et compétences.', 'audience' => 'Profils confirmés', 'is_premium' => true, 'sort_order' => 5, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'sans'],
            ['slug' => 'international', 'name' => 'Élégant', 'category' => 'design', 'description' => 'Photo ronde, bandeau clair et deux colonnes.', 'audience' => 'Présentations soignées', 'is_premium' => true, 'sort_order' => 6, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#0F766E', 'font' => 'serif'],
            ['slug' => 'etudiant', 'name' => 'Bleu professionnel', 'category' => 'original', 'description' => 'Colonne bleue, photo en haut et expériences dans la zone principale.', 'audience' => 'Premiers emplois et profils visuels', 'is_premium' => false, 'sort_order' => 7, 'order' => $student, 'primary' => '#1E3A8A', 'accent' => '#1E40AF', 'font' => 'sans'],
            ['slug' => 'administratif', 'name' => 'Exécutif', 'category' => 'classique', 'description' => 'Bandeau sombre, photo ronde et parcours en deux colonnes pour un poste de responsabilité.', 'audience' => 'Cadres et responsables', 'is_premium' => true, 'sort_order' => 8, 'order' => $standard, 'primary' => '#1C1917', 'accent' => '#B45309', 'font' => 'serif'],
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
