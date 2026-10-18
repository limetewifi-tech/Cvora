<?php

namespace App\Support;

use App\Models\Resume;
use App\Models\ResumeTemplate;

class SampleDocument
{
    public static function resume(ResumeTemplate $template): Resume
    {
        $persona = self::persona($template->slug);
        $resume = new Resume([
            'title' => 'Exemple de mise en page',
            'summary' => $persona['summary'],
            'personal' => [
                'first_name' => $persona['first'],
                'last_name' => $persona['last'],
                'headline' => $persona['headline'],
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
                    'title' => $persona['job'],
                    'company' => $persona['company'],
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

    /**
     * @return array{first: string, last: string, headline: string, summary: string, job: string, company: string}
     */
    private static function persona(string $slug): array
    {
        $sector = ResumeTemplateCatalog::find($slug)['sector'] ?? 'informatique';
        $people = [
            'informatique' => ['Nadia', 'Ilunga', 'Développeuse web', 'Atelier numérique'],
            'administration' => ['Grace', 'Mukendi', 'Assistante de direction', 'Secrétariat central'],
            'comptabilite' => ['Sarah', 'Banza', 'Comptable', 'Cabinet des comptes'],
            'commerce' => ['Joel', 'Tshibanda', 'Conseiller commercial', 'Maison du marché'],
            'ingenierie' => ['Patrick', 'Lumumba', 'Ingénieur travaux', 'Bureau d\'études'],
            'sante' => ['Aline', 'Mbayo', 'Infirmière', 'Centre de santé'],
            'enseignement' => ['Esther', 'Kalala', 'Enseignante', 'École secondaire'],
            'etudiants' => ['David', 'Nsimba', 'Étudiant en gestion', 'Association du campus'],
            'management' => ['Chantal', 'Okito', 'Responsable d\'équipe', 'Direction des opérations'],
            'logistique' => ['Eric', 'Basunga', 'Coordinateur logistique', 'Entrepôt fluvial'],
            'communication' => ['Merveille', 'Sefu', 'Chargée de communication', 'Studio graphique'],
            'hotellerie' => ['Ruth', 'Amisi', 'Réceptionniste', 'Hôtel du fleuve'],
            'construction' => ['Joseph', 'Ngoy', 'Conducteur de travaux', 'Chantier principal'],
            'banque' => ['Divine', 'Kasongo', 'Chargée de clientèle', 'Agence bancaire'],
            'ong' => ['Rachel', 'Bolingo', 'Chargée de projet', 'Association locale'],
        ];
        [$first, $last, $headline, $company] = $people[$sector] ?? $people['informatique'];

        return [
            'first' => $first,
            'last' => $last,
            'headline' => $headline,
            'job' => $headline,
            'company' => $company,
            'summary' => 'Profil fictif de '.$headline.', utilisé seulement pour montrer la mise en page.',
        ];
    }
}
