<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class ScreenTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_authenticated_screens_render(): void
    {
        $user = $this->makeUser('pro', ['role' => UserRole::Admin]);
        $resume = $this->makeResume($user);
        $letter = $user->coverLetters()->create([
            'title' => 'Lettre écran',
            'tone' => 'professionnel',
            'length' => 'standard',
            'body' => 'Madame, Monsieur, je présente ma candidature.',
        ]);

        $this->actingAs($user);

        foreach ([
            route('dashboard'),
            route('cvs.index'),
            route('cvs.create'),
            route('cvs.edit', $resume),
            route('cvs.preview', $resume),
            route('cvs.versions', $resume),
            route('imports.create'),
            route('letters.index'),
            route('letters.create'),
            route('letters.edit', $letter),
            route('assistant.index'),
            route('studio.index'),
            route('jobs.index'),
            route('applications.index'),
            route('billing.index'),
            route('profile.edit'),
            route('settings.edit'),
            route('admin.index'),
            route('admin.users'),
            route('admin.plans'),
            route('admin.templates'),
            route('admin.transactions'),
            route('admin.subscriptions'),
            route('admin.ai'),
            route('admin.settings'),
            route('admin.errors'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
