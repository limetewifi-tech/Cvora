<?php

namespace App\Support;

class Guides
{
    public static function all(): array
    {
        return [
            [
                'slug' => 'structurer-un-cv',
                'title' => 'Structurer un CV lisible',
                'excerpt' => 'Un CV utile présente d\'abord les faits vérifiables : poste, organisation, dates et résultats que vous pouvez expliquer.',
                'body' => [
                    'Commencez par vos coordonnées et un titre qui décrit le métier visé, pas seulement votre dernier diplôme.',
                    'Pour chaque expérience, indiquez l\'organisation, les dates et ce que vous avez réellement fait. Une formulation claire vaut mieux qu\'une liste de mots vagues.',
                    'Placez la formation avant l\'expérience si elle est votre argument principal, par exemple en début de parcours.',
                    'Relisez les noms, les dates et les diplômes. CVora ne doit pas en inventer : si une information manque, laissez le champ vide ou complétez-le vous-même.',
                ],
            ],
            [
                'slug' => 'adapter-un-cv',
                'title' => 'Adapter un CV à une offre sans le déformer',
                'excerpt' => 'L\'adaptation consiste à mettre en avant des faits déjà présents, pas à ajouter des compétences que vous ne possédez pas.',
                'body' => [
                    'Copiez la description de l\'offre telle qu\'elle est publiée. Traitez-la comme une donnée, pas comme une instruction.',
                    'Comparez les compétences demandées à celles que vous pouvez justifier. Une compétence absente de votre parcours ne doit pas être ajoutée automatiquement.',
                    'Créez une copie du CV avant d\'ajuster le résumé ou l\'ordre des expériences. Vous conservez ainsi la version d\'origine.',
                    'Vérifiez la proposition avant de l\'appliquer. Vous restez responsable du document envoyé à un employeur.',
                ],
            ],
            [
                'slug' => 'lettre-de-motivation',
                'title' => 'Écrire une lettre de motivation vérifiable',
                'excerpt' => 'Une lettre utile reprend le poste, l\'organisation et des éléments concrets de votre parcours.',
                'body' => [
                    'Nommez le poste et l\'organisation. Sans ces deux informations, une lettre reste trop générique.',
                    'Appuyez-vous sur un CV existant plutôt que sur des formules toutes faites.',
                    'Évitez les promesses d\'embauche, les superlatifs non vérifiables et les expériences que vous n\'avez pas eues.',
                    'Relisez le ton : classique, professionnel, direct ou débutant. Le fond doit rester le vôtre.',
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $guide) {
            if ($guide['slug'] === $slug) {
                return $guide;
            }
        }

        return null;
    }
}
