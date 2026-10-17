<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'gratuit',
                'name' => 'Gratuit',
                'description' => 'Un CV simple, des modèles essentiels et un aperçu. Les exportations et les lettres demandent une offre supérieure.',
                'price' => '0.00',
                'currency' => 'CDF',
                'interval' => 'month',
                'resume_limit' => 1,
                'letter_limit' => 0,
                'ai_credits' => 5,
                'sort_order' => 1,
                'features' => [
                    'preview' => true,
                    'export_pdf' => false,
                    'export_docx' => false,
                    'premium_templates' => false,
                    'resume_refresh' => false,
                    'cover_letters' => false,
                    'version_history' => false,
                    'job_adaptation' => false,
                ],
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'description' => 'Modèles premium, exportations PDF et Word, lettres de motivation et actualisation assistée.',
                'price' => '15000.00',
                'currency' => 'CDF',
                'interval' => 'month',
                'resume_limit' => 5,
                'letter_limit' => 10,
                'ai_credits' => 100,
                'sort_order' => 2,
                'features' => [
                    'preview' => true,
                    'export_pdf' => true,
                    'export_docx' => true,
                    'premium_templates' => true,
                    'resume_refresh' => true,
                    'cover_letters' => true,
                    'version_history' => false,
                    'job_adaptation' => false,
                ],
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Plusieurs documents, historique des versions, adaptation aux offres et un quota IA plus élevé.',
                'price' => '35000.00',
                'currency' => 'CDF',
                'interval' => 'month',
                'resume_limit' => null,
                'letter_limit' => null,
                'ai_credits' => 400,
                'sort_order' => 3,
                'features' => [
                    'preview' => true,
                    'export_pdf' => true,
                    'export_docx' => true,
                    'premium_templates' => true,
                    'resume_refresh' => true,
                    'cover_letters' => true,
                    'version_history' => true,
                    'job_adaptation' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['is_active' => true],
            );
        }
    }
}
