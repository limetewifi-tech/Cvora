<?php

namespace Tests;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use App\Support\ResumeContent;
use Database\Seeders\PlanSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TemplateSeeder;

trait CreatesCvoraWorld
{
    protected function seedCatalog(): void
    {
        $this->seed([
            PlanSeeder::class,
            TemplateSeeder::class,
            SettingSeeder::class,
        ]);
    }

    protected function makeUser(string $plan = 'gratuit', array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->profile()->create([
            'first_name' => 'Amina',
            'last_name' => 'Kabila',
            'headline' => 'Analyste',
            'city' => 'Kinshasa',
            'country' => 'République démocratique du Congo',
            'phone' => '+243810000000',
            'summary' => 'Analyste à Kinshasa.',
            'preferences' => ['language' => 'fr', 'email_notifications' => true],
        ]);

        $planModel = Plan::query()->where('slug', $plan)->firstOrFail();
        $user->subscription()->create([
            'plan_id' => $planModel->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => $planModel->isFree() ? null : now()->addMonth(),
            'credits_remaining' => (int) $planModel->ai_credits,
            'credits_reset_at' => now()->addMonth(),
        ]);

        return $user->fresh(['profile', 'subscription.plan']);
    }

    protected function makeResume(User $user, array $overrides = []): Resume
    {
        $template = ResumeTemplate::query()->where('slug', $overrides['template'] ?? 'moderne')->firstOrFail();

        return $user->resumes()->create([
            'template_id' => $template->id,
            'title' => $overrides['title'] ?? 'CV principal',
            'status' => 'draft',
            'source' => 'editor',
            'summary' => $overrides['summary'] ?? 'Analyste à Kinshasa.',
            'personal' => ResumeContent::normalizePersonal([
                'first_name' => 'Amina',
                'last_name' => 'Kabila',
                'headline' => 'Analyste',
                'email' => $user->email,
                'city' => 'Kinshasa',
                'country' => 'République démocratique du Congo',
            ]),
            'content' => ResumeContent::normalizeContent([
                'experiences' => [[
                    'title' => 'Analyste',
                    'company' => 'Banque exemple',
                    'location' => 'Kinshasa',
                    'start_date' => '2022',
                    'end_date' => '2024',
                    'description' => 'Suivi des dossiers clients.',
                ]],
                'education' => [[
                    'degree' => 'Licence en gestion',
                    'school' => 'Université de Kinshasa',
                    'location' => 'Kinshasa',
                    'start_date' => '2018',
                    'end_date' => '2021',
                    'description' => 'Parcours en gestion.',
                ]],
                'skills' => [[
                    'name' => 'Excel',
                    'level' => 'Avancé',
                ]],
            ]),
            'section_order' => $template->default_section_order ?: ResumeContent::defaultOrder(),
            'theme' => $template->default_theme ?: ['primary' => '#2563EB', 'accent' => '#1F2937'],
            'lock_version' => 0,
        ]);
    }

    protected function resumeForm(Resume $resume, array $overrides = []): array
    {
        $payload = [
            'title' => $resume->title,
            'summary' => $resume->summary,
            'template_id' => $resume->template_id,
            'lock_version' => $resume->lock_version,
            'status' => $resume->status,
            'sections_present' => ResumeContent::sections(),
            'section_order' => $resume->section_order,
            'theme' => $resume->theme,
            'personal' => $resume->personal,
        ];

        foreach (ResumeContent::sections() as $section) {
            $payload[$section] = $resume->content[$section] ?? [];
        }

        return array_replace_recursive($payload, $overrides);
    }
}
