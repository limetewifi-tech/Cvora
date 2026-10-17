<?php

namespace App\Support;

use Illuminate\Support\Str;

class ResumeContent
{
    public const SECTIONS = [
        'experiences',
        'education',
        'skills',
        'languages',
        'certifications',
        'projects',
        'interests',
        'references',
    ];

    public static function sections(): array
    {
        return self::SECTIONS;
    }

    public static function emptyContent(): array
    {
        return array_fill_keys(self::SECTIONS, []);
    }

    public static function defaultOrder(): array
    {
        return self::SECTIONS;
    }

    public static function labels(): array
    {
        return [
            'experiences' => ['fr' => 'Expérience professionnelle', 'en' => 'Experience', 'optional' => false],
            'education' => ['fr' => 'Formation et diplômes', 'en' => 'Education', 'optional' => false],
            'skills' => ['fr' => 'Compétences', 'en' => 'Skills', 'optional' => false],
            'languages' => ['fr' => 'Langues', 'en' => 'Languages', 'optional' => false],
            'certifications' => ['fr' => 'Certifications', 'en' => 'Certifications', 'optional' => false],
            'projects' => ['fr' => 'Projets', 'en' => 'Projects', 'optional' => false],
            'interests' => ['fr' => 'Centres d\'intérêt', 'en' => 'Interests', 'optional' => true],
            'references' => ['fr' => 'Références', 'en' => 'References', 'optional' => true],
        ];
    }

    public static function fields(string $section): array
    {
        return match ($section) {
            'experiences' => [
                ['name' => 'title', 'label' => 'Poste', 'type' => 'text'],
                ['name' => 'company', 'label' => 'Organisation', 'type' => 'text'],
                ['name' => 'location', 'label' => 'Lieu', 'type' => 'text'],
                ['name' => 'start_date', 'label' => 'Début', 'type' => 'text'],
                ['name' => 'end_date', 'label' => 'Fin', 'type' => 'text'],
                ['name' => 'current', 'label' => 'Poste actuel', 'type' => 'checkbox'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
            'education' => [
                ['name' => 'degree', 'label' => 'Diplôme', 'type' => 'text'],
                ['name' => 'school', 'label' => 'Établissement', 'type' => 'text'],
                ['name' => 'location', 'label' => 'Lieu', 'type' => 'text'],
                ['name' => 'start_date', 'label' => 'Début', 'type' => 'text'],
                ['name' => 'end_date', 'label' => 'Fin', 'type' => 'text'],
                ['name' => 'description', 'label' => 'Détails', 'type' => 'textarea'],
            ],
            'skills' => [
                ['name' => 'name', 'label' => 'Compétence', 'type' => 'text'],
                ['name' => 'level', 'label' => 'Niveau', 'type' => 'text'],
            ],
            'languages' => [
                ['name' => 'name', 'label' => 'Langue', 'type' => 'text'],
                ['name' => 'level', 'label' => 'Niveau', 'type' => 'text'],
            ],
            'certifications' => [
                ['name' => 'name', 'label' => 'Intitulé', 'type' => 'text'],
                ['name' => 'issuer', 'label' => 'Organisme', 'type' => 'text'],
                ['name' => 'date', 'label' => 'Date', 'type' => 'text'],
            ],
            'projects' => [
                ['name' => 'name', 'label' => 'Projet', 'type' => 'text'],
                ['name' => 'url', 'label' => 'Lien', 'type' => 'text'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ],
            'interests' => [
                ['name' => 'name', 'label' => 'Centre d\'intérêt', 'type' => 'text'],
            ],
            'references' => [
                ['name' => 'name', 'label' => 'Nom', 'type' => 'text'],
                ['name' => 'role', 'label' => 'Fonction', 'type' => 'text'],
                ['name' => 'contact', 'label' => 'Contact', 'type' => 'text'],
            ],
            default => [],
        };
    }

    public static function blank(string $section): array
    {
        $item = ['id' => (string) Str::uuid()];

        foreach (self::fields($section) as $field) {
            $item[$field['name']] = $field['type'] === 'checkbox' ? false : '';
        }

        return $item;
    }

    public static function emptyPersonal(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'headline' => '',
            'email' => '',
            'phone' => '',
            'city' => '',
            'country' => '',
            'address' => '',
            'linkedin' => '',
            'website' => '',
        ];
    }

    public static function normalizePersonal(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $personal = self::emptyPersonal();

        foreach (array_keys($personal) as $key) {
            $personal[$key] = self::clean($input[$key] ?? '', 160);
        }

        return $personal;
    }

    public static function normalizeTheme(mixed $input, array $fallback = []): array
    {
        $input = is_array($input) ? $input : [];
        $primary = self::hex($input['primary'] ?? null) ?? self::hex($fallback['primary'] ?? null) ?? '#2563EB';
        $accent = self::hex($input['accent'] ?? null) ?? self::hex($fallback['accent'] ?? null) ?? '#1F2937';

        return ['primary' => $primary, 'accent' => $accent];
    }

    public static function normalizeOrder(mixed $input, ?array $preferred = null): array
    {
        $preferred ??= self::defaultOrder();
        $given = is_array($input) ? $input : [];
        $order = [];

        foreach ($given as $key) {
            if (in_array($key, self::SECTIONS, true) && ! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        foreach ($preferred as $key) {
            if (in_array($key, self::SECTIONS, true) && ! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        foreach (self::SECTIONS as $key) {
            if (! in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        return $order;
    }

    public static function normalizeContent(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $content = [];

        foreach (self::SECTIONS as $section) {
            $rows = $input[$section] ?? [];
            $content[$section] = [];

            if (! is_array($rows)) {
                continue;
            }

            foreach (array_slice(array_values($rows), 0, 40) as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $content[$section][] = self::normalizeItem($section, $row);
            }
        }

        return $content;
    }

    public static function normalizeItem(string $section, array $row): array
    {
        $item = self::blank($section);
        $item['id'] = self::clean($row['id'] ?? '', 64) ?: $item['id'];

        foreach (self::fields($section) as $field) {
            $name = $field['name'];

            if ($field['type'] === 'checkbox') {
                $item[$name] = filter_var($row[$name] ?? false, FILTER_VALIDATE_BOOL);
                continue;
            }

            $limit = $field['type'] === 'textarea' ? 4000 : 200;
            $item[$name] = self::clean($row[$name] ?? '', $limit, $field['type'] === 'textarea');
        }

        return $item;
    }

    public static function applyAction(array $input): array
    {
        $action = (string) ($input['action'] ?? '');

        if ($action === '' || ! preg_match('/^(add|remove|move):([a-z_]+)(?::(\d+)(?::(up|down))?)?$/', $action, $matches)) {
            return $input;
        }

        $section = $matches[2];

        if (! in_array($section, self::SECTIONS, true)) {
            return $input;
        }

        $items = is_array($input[$section] ?? null) ? array_values($input[$section]) : [];

        if ($matches[1] === 'add' && count($items) < 40) {
            $items[] = self::blank($section);
        }

        if ($matches[1] === 'remove' && isset($matches[3]) && isset($items[(int) $matches[3]])) {
            array_splice($items, (int) $matches[3], 1);
        }

        if ($matches[1] === 'move' && isset($matches[3], $matches[4])) {
            $index = (int) $matches[3];
            $target = $matches[4] === 'down' ? $index + 1 : $index - 1;

            if (isset($items[$index], $items[$target])) {
                [$items[$index], $items[$target]] = [$items[$target], $items[$index]];
            }
        }

        $input[$section] = array_values($items);

        return $input;
    }

    public static function itemIsFilled(array $item): bool
    {
        foreach ($item as $key => $value) {
            if (in_array($key, ['id', 'current'], true)) {
                continue;
            }

            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }

    public static function clean(mixed $value, int $limit, bool $keepLines = false): string
    {
        $text = strip_tags((string) $value);
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        if ($keepLines) {
            $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
            $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        } else {
            $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        }

        return mb_substr(trim($text), 0, $limit);
    }

    public static function hex(mixed $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : null;
    }
}
