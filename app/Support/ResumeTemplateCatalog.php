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
            ['slug' => 'moderne', 'name' => 'Moderne', 'description' => 'Bandeau coloré et sections aérées, pour une lecture rapide.', 'audience' => 'Jeunes diplômés et profils polyvalents', 'is_premium' => false, 'sort_order' => 1, 'order' => $standard, 'primary' => '#2563EB', 'accent' => '#1F2937'],
            ['slug' => 'classique', 'name' => 'Classique', 'description' => 'Mise en page centrée, serif, pour les contextes formels.', 'audience' => 'Candidatures institutionnelles', 'is_premium' => false, 'sort_order' => 2, 'order' => $standard, 'primary' => '#1F2937', 'accent' => '#2563EB'],
            ['slug' => 'minimaliste', 'name' => 'Minimaliste', 'description' => 'Beaucoup d\'air, peu de décoration, le texte d\'abord.', 'audience' => 'Profils qui veulent une lecture directe', 'is_premium' => false, 'sort_order' => 3, 'order' => $standard, 'primary' => '#111827', 'accent' => '#6B7280'],
            ['slug' => 'etudiant', 'name' => 'Étudiant', 'description' => 'La formation et les projets passent avant l\'expérience.', 'audience' => 'Étudiants et premiers emplois', 'is_premium' => false, 'sort_order' => 4, 'order' => $student, 'primary' => '#2563EB', 'accent' => '#10B981'],
            ['slug' => 'experimente', 'name' => 'Professionnel expérimenté', 'description' => 'En-tête sombre et filet accentué, pour un parcours déjà long.', 'audience' => 'Cadres et profils confirmés', 'is_premium' => true, 'sort_order' => 5, 'order' => $standard, 'primary' => '#1F2937', 'accent' => '#F59E0B'],
            ['slug' => 'technique', 'name' => 'Technique et informatique', 'description' => 'Compétences et projets mis en avant, style compact.', 'audience' => 'Développement, systèmes, données', 'is_premium' => true, 'sort_order' => 6, 'order' => $technical, 'primary' => '#0F172A', 'accent' => '#38BDF8'],
            ['slug' => 'administratif', 'name' => 'Administratif', 'description' => 'Identité en tableau et sections linéaires, façon dossier.', 'audience' => 'Administration, secrétariat, gestion', 'is_premium' => true, 'sort_order' => 7, 'order' => $standard, 'primary' => '#1E3A8A', 'accent' => '#1F2937'],
            ['slug' => 'international', 'name' => 'International', 'description' => 'Intitulés en français et en anglais, pour une lecture transfrontalière.', 'audience' => 'Candidatures hors du marché local', 'is_premium' => true, 'sort_order' => 8, 'order' => $standard, 'primary' => '#111827', 'accent' => '#2563EB'],
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
            ],
        ];
    }
}
