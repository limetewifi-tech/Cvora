<?php

namespace App\Services\Documents;

use App\Models\Resume;
use App\Support\ResumeContent;

class ResumePresenter
{
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

        $contact = array_values(array_filter([
            $personal['email'],
            $personal['phone'],
            trim($personal['address'].' '.$personal['city'].' '.$personal['country']),
            $personal['linkedin'],
            $personal['website'],
        ]));

        return [
            'slug' => $resume->template?->slug ?? 'moderne',
            'name' => trim($personal['first_name'].' '.$personal['last_name']) ?: 'Prénom Nom',
            'headline' => $personal['headline'],
            'contact' => $contact,
            'summary' => trim((string) $resume->summary),
            'sections' => $sections,
            'primary' => $theme['primary'],
            'accent' => $theme['accent'],
            'font' => $theme['font'],
            'size' => $theme['size'],
        ];
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
