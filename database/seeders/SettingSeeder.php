<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'feature_ai' => ['1', 'features'],
            'feature_import' => ['1', 'features'],
            'feature_letters' => ['1', 'features'],
            'feature_payments' => ['1', 'features'],
            'feature_exports' => ['1', 'features'],
            'ai_driver' => ['', 'ai'],
            'ai_fallback_driver' => ['', 'ai'],
            'anthropic_model' => ['', 'ai'],
            'gemini_model' => ['', 'ai'],
            'groq_model' => ['', 'ai'],
            'legal_name' => ['', 'legal'],
            'legal_address' => ['', 'legal'],
            'legal_email' => ['', 'legal'],
            'legal_rccm' => ['', 'legal'],
            'contact_email' => ['', 'legal'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group],
            );
        }
    }
}
