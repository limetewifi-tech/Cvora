<?php

namespace App\Support;

class ResumeReadiness
{
    /**
     * @param  array<string, mixed>  $form
     * @return array<int, array{label: string, jump: string, done: bool, mode: string, fields: array<int, string>, prefix: string}>
     */
    public static function checks(array $form): array
    {
        $personal = is_array($form['personal'] ?? null) ? $form['personal'] : [];

        return [
            [
                'label' => 'Prénom et nom',
                'jump' => 'identite',
                'done' => filled($personal['first_name'] ?? null) && filled($personal['last_name'] ?? null),
                'mode' => 'all',
                'fields' => ['personal[first_name]', 'personal[last_name]'],
                'prefix' => '',
            ],
            [
                'label' => 'Titre visé',
                'jump' => 'identite',
                'done' => filled($personal['headline'] ?? null),
                'mode' => 'all',
                'fields' => ['personal[headline]'],
                'prefix' => '',
            ],
            [
                'label' => 'E-mail ou téléphone',
                'jump' => 'identite',
                'done' => filled($personal['email'] ?? null) || filled($personal['phone'] ?? null),
                'mode' => 'any',
                'fields' => ['personal[email]', 'personal[phone]'],
                'prefix' => '',
            ],
            [
                'label' => 'Résumé professionnel',
                'jump' => 'resume',
                'done' => filled($form['summary'] ?? null),
                'mode' => 'all',
                'fields' => ['summary'],
                'prefix' => '',
            ],
            [
                'label' => 'Une expérience avec un poste et une organisation',
                'jump' => 'experiences',
                'done' => self::rowFilled($form['experiences'] ?? [], ['title', 'company']),
                'mode' => 'row',
                'fields' => ['title', 'company'],
                'prefix' => 'experiences',
            ],
            [
                'label' => 'Une formation',
                'jump' => 'education',
                'done' => self::rowFilled($form['education'] ?? [], ['degree']) || self::rowFilled($form['education'] ?? [], ['school']),
                'mode' => 'row-any',
                'fields' => ['degree', 'school'],
                'prefix' => 'education',
            ],
            [
                'label' => 'Une compétence',
                'jump' => 'skills',
                'done' => self::rowFilled($form['skills'] ?? [], ['name']),
                'mode' => 'row',
                'fields' => ['name'],
                'prefix' => 'skills',
            ],
        ];
    }

    /**
     * @param  mixed  $rows
     * @param  array<int, string>  $keys
     */
    private static function rowFilled(mixed $rows, array $keys): bool
    {
        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $complete = true;

            foreach ($keys as $key) {
                if (! filled($row[$key] ?? null)) {
                    $complete = false;
                }
            }

            if ($complete) {
                return true;
            }
        }

        return false;
    }
}
