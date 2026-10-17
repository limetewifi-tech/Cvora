<?php

namespace App\Http\Controllers;

use App\Services\SettingsRepository;

class LegalController extends Controller
{
    public function privacy(SettingsRepository $settings)
    {
        return view('public.legal', [
            'title' => 'Confidentialité',
            'settings' => $settings,
            'kind' => 'privacy',
        ]);
    }

    public function terms(SettingsRepository $settings)
    {
        return view('public.legal', [
            'title' => 'Conditions d\'utilisation',
            'settings' => $settings,
            'kind' => 'terms',
        ]);
    }

    public function notice(SettingsRepository $settings)
    {
        return view('public.legal', [
            'title' => 'Mentions légales',
            'settings' => $settings,
            'kind' => 'notice',
        ]);
    }
}
