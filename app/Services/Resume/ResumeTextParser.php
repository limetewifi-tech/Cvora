<?php

namespace App\Services\Resume;

use App\Support\ResumeContent;

class ResumeTextParser
{
    private const HEADINGS = [
        'experiences' => '/^(exp[ée]riences?(?: professionnelles)?|parcours professionnel|experience)$/iu',
        'education' => '/^(formations?|études|etudes|[ée]ducation|dipl[ôo]mes?)$/iu',
        'skills' => '/^(comp[ée]tences?|skills|savoir-faire)$/iu',
        'languages' => '/^(langues?|languages)$/iu',
        'certifications' => '/^(certifications?|certificats?)$/iu',
        'projects' => '/^(projets?|projects)$/iu',
        'interests' => '/^(centres? d[\'’]int[ée]r[êe]ts?|loisirs|int[ée]r[êe]ts)$/iu',
        'references' => '/^(r[ée]f[ée]rences?)$/iu',
        'summary' => '/^(r[ée]sum[ée]|profil|à propos|a propos|objectif)$/iu',
    ];

    public function parse(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($line) => $line !== ''));

        $personal = ResumeContent::emptyPersonal();
        $personal['email'] = $this->firstMatch($text, '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu') ?? '';
        $personal['phone'] = $this->firstMatch($text, '/(?:\+\d{1,3}[\s.\-]?)?(?:\(0\)[\s.\-]?)?(?:0|\+243)[\d\s.\-]{8,}/u') ?? '';

        $sections = [];
        $current = 'header';
        $sections[$current] = [];

        foreach ($lines as $line) {
            $matched = null;

            foreach (self::HEADINGS as $key => $pattern) {
                if (preg_match($pattern, $line)) {
                    $matched = $key;
                    break;
                }
            }

            if ($matched) {
                $current = $matched;
                $sections[$current] ??= [];
                continue;
            }

            $sections[$current][] = $line;
        }

        foreach ($sections['header'] ?? [] as $line) {
            if ($personal['email'] && str_contains(mb_strtolower($line), mb_strtolower($personal['email']))) {
                continue;
            }

            if ($personal['phone'] && str_contains($line, trim($personal['phone']))) {
                continue;
            }

            if ($personal['first_name'] === '' && mb_strlen($line) <= 80 && ! str_contains($line, '@')) {
                $parts = preg_split('/\s+/', $line, 2) ?: [];
                $personal['first_name'] = $parts[0] ?? '';
                $personal['last_name'] = $parts[1] ?? '';
                continue;
            }

            if ($personal['headline'] === '' && mb_strlen($line) <= 120) {
                $personal['headline'] = $line;
            }
        }

        if (preg_match('/\b(Kinshasa|Lubumbashi|Goma|Bukavu|Matadi|Kisangani|Kolwezi|Mbuji-Mayi|Kananga|Likasi)\b/u', $text, $city)) {
            $personal['city'] = $city[1];
            $personal['country'] = 'République démocratique du Congo';
        }

        $content = ResumeContent::emptyContent();
        $content['experiences'] = $this->blocks($sections['experiences'] ?? [], 'experience');
        $content['education'] = $this->blocks($sections['education'] ?? [], 'education');
        $content['skills'] = $this->named($sections['skills'] ?? [], 'skill');
        $content['languages'] = $this->named($sections['languages'] ?? [], 'language');
        $content['certifications'] = $this->named($sections['certifications'] ?? [], 'certification');
        $content['projects'] = $this->named($sections['projects'] ?? [], 'project');
        $content['interests'] = $this->named($sections['interests'] ?? [], 'interest');
        $content['references'] = $this->named($sections['references'] ?? [], 'reference');

        return [
            'personal' => ResumeContent::normalizePersonal($personal),
            'summary' => ResumeContent::clean(implode("\n", $sections['summary'] ?? []), 4000, true),
            'content' => ResumeContent::normalizeContent($content),
            'detected' => $this->detectedKeys($personal, $sections),
        ];
    }

    private function blocks(array $lines, string $type): array
    {
        $chunks = $this->chunk($lines);
        $items = [];

        foreach ($chunks as $chunk) {
            $item = ResumeContent::blank($type === 'experience' ? 'experiences' : 'education');
            $head = $chunk[0] ?? '';
            $dates = null;
            $body = [];

            foreach (array_slice($chunk, 1) as $line) {
                if ($dates === null && preg_match('/\b(19|20)\d{2}\b/u', $line)) {
                    $dates = $line;
                    continue;
                }

                $body[] = $line;
            }

            if (preg_match('/^(.+?)\s+[—–\-]\s+(.+)$/u', $head, $parts)) {
                $left = trim($parts[1]);
                $right = trim($parts[2]);
            } else {
                $left = $head;
                $right = '';
            }

            $rightParts = array_map('trim', explode(',', $right, 2));

            if ($type === 'experience') {
                $item['title'] = $left;
                $item['company'] = $rightParts[0] ?? '';
                $item['location'] = $rightParts[1] ?? '';
                $item['description'] = implode("\n", $body);
            } else {
                $item['degree'] = $left;
                $item['school'] = $rightParts[0] ?? '';
                $item['location'] = $rightParts[1] ?? '';
                $item['description'] = implode("\n", $body);
            }

            if ($dates && preg_match_all('/\b(?:19|20)\d{2}|aujourd\'hui|present|en cours/iu', $dates, $found)) {
                $item['start_date'] = $found[0][0] ?? '';
                $item['end_date'] = $found[0][1] ?? '';
                $item['current'] = (bool) preg_match('/aujourd\'hui|present|en cours/iu', $dates);
            }

            $items[] = $item;
        }

        return $items;
    }

    private function named(array $lines, string $type): array
    {
        $flat = [];

        foreach ($lines as $line) {
            foreach (preg_split('/[,;•·]/u', $line) ?: [] as $piece) {
                $piece = trim($piece);
                if ($piece !== '') {
                    $flat[] = $piece;
                }
            }
        }

        return array_map(function (string $piece) use ($type) {
            [$name, $level] = array_pad(preg_split('/\s+[—–:\-]\s+/u', $piece, 2) ?: [], 2, '');

            return match ($type) {
                'skill' => array_merge(ResumeContent::blank('skills'), ['name' => $name, 'level' => $level]),
                'language' => array_merge(ResumeContent::blank('languages'), ['name' => $name, 'level' => $level]),
                'certification' => array_merge(ResumeContent::blank('certifications'), ['name' => $name, 'issuer' => $level]),
                'project' => array_merge(ResumeContent::blank('projects'), ['name' => $name, 'description' => $level]),
                'reference' => array_merge(ResumeContent::blank('references'), ['name' => $name, 'role' => $level]),
                default => array_merge(ResumeContent::blank('interests'), ['name' => $name]),
            };
        }, $flat);
    }

    private function chunk(array $lines): array
    {
        $chunks = [];
        $current = [];

        foreach ($lines as $line) {
            if ($line === '---' && $current) {
                $chunks[] = $current;
                $current = [];
                continue;
            }

            $current[] = $line;
        }

        if ($current) {
            $chunks[] = $current;
        }

        return $chunks;
    }

    private function firstMatch(string $text, string $pattern): ?string
    {
        return preg_match($pattern, $text, $matches) ? trim($matches[0]) : null;
    }

    private function detectedKeys(array $personal, array $sections): array
    {
        $keys = [];

        foreach (['first_name', 'email', 'phone', 'city', 'headline'] as $key) {
            if (filled($personal[$key] ?? null)) {
                $keys[] = $key;
            }
        }

        foreach (['summary', 'experiences', 'education', 'skills', 'languages'] as $key) {
            if (! empty($sections[$key])) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
