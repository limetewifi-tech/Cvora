<?php

namespace App\Support;

use App\Models\Resume;
use App\Models\ResumeTemplate;

class SampleDocument
{
    public static function resume(ResumeTemplate $template): Resume
    {
        $resume = new Resume([
            'title' => 'Exemple de mise en page',
            'summary' => 'Ce texte fictif montre uniquement la mise en page. Remplacez-le par votre propre parcours.',
            'personal' => [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'headline' => 'Intitulé du poste visé',
                'email' => 'prenom.nom@example.com',
                'phone' => '+243 000 000 000',
                'city' => 'Kinshasa',
                'country' => 'République démocratique du Congo',
                'address' => '',
                'linkedin' => 'linkedin.com/in/exemple',
                'website' => '',
            ],
            'content' => [
                'experiences' => [[
                    'id' => 'exemple-exp',
                    'title' => 'Intitulé du poste',
                    'company' => 'Organisation exemple',
                    'location' => 'Kinshasa',
                    'start_date' => '2023',
                    'end_date' => '2024',
                    'current' => false,
                    'description' => 'Décrivez ici une mission réelle, avec des faits que vous pouvez expliquer en entretien.',
                ]],
                'education' => [[
                    'id' => 'exemple-edu',
                    'degree' => 'Diplôme exemple',
                    'school' => 'Établissement exemple',
                    'location' => 'Kinshasa',
                    'start_date' => '2020',
                    'end_date' => '2023',
                    'description' => 'Mentionnez uniquement un diplôme obtenu ou en cours.',
                ]],
                'skills' => [
                    ['id' => 's1', 'name' => 'Compétence une', 'level' => 'Pratique'],
                    ['id' => 's2', 'name' => 'Compétence deux', 'level' => 'À préciser'],
                ],
                'languages' => [
                    ['id' => 'l1', 'name' => 'Français', 'level' => 'Courant'],
                    ['id' => 'l2', 'name' => 'Anglais', 'level' => 'Niveau à préciser'],
                ],
                'certifications' => [],
                'projects' => [[
                    'id' => 'p1',
                    'name' => 'Projet exemple',
                    'url' => '',
                    'description' => 'Un projet personnel ou académique, décrit sobrement.',
                ]],
                'interests' => [['id' => 'i1', 'name' => 'Lecture']],
                'references' => [],
            ],
            'section_order' => $template->default_section_order ?: ResumeContent::defaultOrder(),
            'theme' => $template->default_theme ?: ['primary' => '#2563EB', 'accent' => '#1F2937'],
        ]);

        $resume->setRelation('template', $template);

        return $resume;
    }
}
