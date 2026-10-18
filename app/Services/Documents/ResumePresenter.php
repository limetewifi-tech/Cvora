<?php

namespace App\Services\Documents;

use App\Models\Resume;
use App\Support\ResumeContent;

class ResumePresenter
{
    public function __construct(private ResumePhoto $photos) {}

    public function present(Resume $resume): array
    {
        $resume->loadMissing('template');
        $personal = ResumeContent::normalizePersonal($resume->personal ?? []);
        $content = ResumeContent::normalizeContent($resume->content ?? []);
        $order = ResumeContent::normalizeOrder($resume->section_order, $resume->template?->default_section_order);
        $theme = ResumeContent::normalizeTheme($resume->theme, $resume->template?->default_theme ?? []);
        $labels = ResumeContent::labels();
        $sections = [];

        foreach ($order as $key) {
            $items = array_values(array_filter($content[$key] ?? [], fn ($item) => ResumeContent::itemIsFilled($item)));

            if ($items === []) {
                continue;
            }

            $sections[] = [
                'key' => $key,
                'label' => $labels[$key]['fr'],
                'label_en' => $labels[$key]['en'],
                'items' => array_map(fn ($item) => $this->item($key, $item), $items),
            ];
        }

        $place = trim(implode(', ', array_filter([$personal['address'], $personal['city'], $personal['country']])));
        $lines = array_values(array_filter([
            $this->line('E-mail', $personal['email']),
            $this->line('Téléphone', $personal['phone']),
            $this->line('Adresse', $place),
            $this->line('LinkedIn', $personal['linkedin']),
            $this->line('Site', $personal['website']),
        ]));
        $slug = $resume->template?->slug ?? 'moderne';
        $layout = ResumePhoto::layout($slug);
        $shape = ResumePhoto::shape($layout);
        $name = trim($personal['first_name'].' '.$personal['last_name']) ?: 'Prénom Nom';
        $initials = $this->initials($name);
        $photo = $this->photos->dataUri($resume->photo_path, $shape, ResumePhoto::backdrop($layout, $theme['primary']));
        $summary = trim((string) $resume->summary);

        return [
            'slug' => $slug,
            'layout' => $layout,
            'name' => $name,
            'initials' => $initials,
            'headline' => $personal['headline'],
            'contact' => array_column($lines, 'value'),
            'lines' => $lines,
            'summary' => $summary,
            'density' => $this->density($summary, $sections),
            'sections' => $sections,
            'by' => collect($sections)->keyBy('key')->all(),
            'photo' => $photo,
            'mark' => $photo ?? $this->photos->monogramUri($initials, $shape, $layout, $theme['primary']),
            'photo_shape' => $shape,
            'primary' => $theme['primary'],
            'accent' => $theme['accent'],
            'font' => $theme['font'],
            'size' => $theme['size'],
        ];
    }

    /**
     * @return array{label: string, value: string}|null
     */
    private function line(string $label, string $value): ?array
    {
        $value = trim($value);

        return $value === '' ? null : ['label' => $label, 'value' => $value];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    private function density(string $summary, array $sections): string
    {
        $chars = mb_strlen($summary);

        foreach ($sections as $section) {
            foreach ($section['items'] as $item) {
                $chars += mb_strlen(implode('', [
                    $item['primary'] ?? '',
                    $item['secondary'] ?? '',
                    $item['dates'] ?? '',
                    $item['body'] ?? '',
                ]));
            }
        }

        if ($chars < 900) {
            return 'roomy';
        }

        if ($chars < 2200) {
            return 'balanced';
        }

        return 'compact';
    }

    private function initials(string $name): string
    {
        $initials = '';

        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $part) {
            if ($part !== '') {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        $initials = mb_substr($initials, 0, 2);

        return $initials !== '' ? $initials : 'CV';
    }

    private function item(string $section, array $item): array
    {
        $dates = trim(($item['start_date'] ?? '').(($item['end_date'] ?? '') !== '' || ($item['current'] ?? false) ? ' — '.(($item['current'] ?? false) ? 'Aujourd\'hui' : ($item['end_date'] ?? '')) : ''));

        return match ($section) {
            'experiences' => [
                'primary' => $item['title'] ?: 'Expérience',
                'secondary' => trim(implode(' · ', array_filter([$item['company'] ?? '', $item['location'] ?? '']))),
                'dates' => $dates,
                'body' => $item['description'] ?? '',
            ],
            'education' => [
                'primary' => $item['degree'] ?: 'Formation',
                'secondary' => trim(implode(' · ', array_filter([$item['school'] ?? '', $item['location'] ?? '']))),
                'dates' => $dates,
                'body' => $item['description'] ?? '',
            ],
            'skills', 'languages', 'interests' => [
                'primary' => $item['name'] ?? '',
                'secondary' => $item['level'] ?? '',
                'dates' => '',
                'body' => '',
            ],
            'certifications' => [
                'primary' => $item['name'] ?? '',
                'secondary' => $item['issuer'] ?? '',
                'dates' => $item['date'] ?? '',
                'body' => '',
            ],
            'projects' => [
                'primary' => $item['name'] ?? '',
                'secondary' => $item['url'] ?? '',
                'dates' => '',
                'body' => $item['description'] ?? '',
            ],
            default => [
                'primary' => $item['name'] ?? '',
                'secondary' => trim(implode(' · ', array_filter([$item['role'] ?? '', $item['contact'] ?? '']))),
                'dates' => '',
                'body' => '',
            ],
        };
    }
}
