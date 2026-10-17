<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Gate;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class IsolationTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_another_user_and_an_admin_cannot_open_a_private_resume(): void
    {
        $owner = $this->makeUser();
        $resume = $this->makeResume($owner, ['summary' => 'SECRET-CV-AMINA']);
        $other = $this->makeUser();
        $admin = $this->makeUser('pro', ['role' => UserRole::Admin]);

        $this->actingAs($other)->get(route('cvs.edit', $resume))->assertNotFound();
        $this->actingAs($admin)->get(route('cvs.edit', $resume))->assertNotFound();
        $this->actingAs($admin)->get(route('cvs.preview', $resume))->assertNotFound();

        $this->assertFalse(Gate::forUser($admin)->allows('view', $resume));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $resume));

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertDontSee('SECRET-CV-AMINA');
    }
}
