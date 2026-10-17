<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Plan;
use Database\Seeders\DemoSeeder;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_only_an_admin_can_open_the_panel_and_change_a_price(): void
    {
        $user = $this->makeUser();
        $admin = $this->makeUser('pro', ['role' => UserRole::Admin]);
        $plan = Plan::query()->where('slug', 'premium')->firstOrFail();

        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.index'))->assertOk()->assertSee('Comptes');
        $this->get(route('admin.plans'))->assertOk();

        $this->put(route('admin.plans.update', $plan), [
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Offre ajustée par l\'administration.',
            'price' => '18000',
            'currency' => 'CDF',
            'interval' => 'month',
            'resume_limit' => 5,
            'letter_limit' => 10,
            'ai_credits' => 100,
            'sort_order' => 2,
            'is_active' => '1',
            'features' => [
                'export_pdf' => '1',
                'export_docx' => '1',
                'premium_templates' => '1',
                'cover_letters' => '1',
                'resume_refresh' => '1',
            ],
        ])->assertSessionHas('success');

        $this->assertSame('18000.00', Plan::query()->where('slug', 'premium')->value('price'));
        $this->get(route('pricing'))->assertSee('18 000');
    }

    public function test_the_last_active_admin_cannot_be_removed(): void
    {
        $admin = $this->makeUser('pro', ['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'role' => 'user',
            'is_active' => '1',
        ])->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_demo_accounts_are_not_seeded_unless_explicitly_enabled(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'demo@cvora.test']);
        $this->assertDatabaseMissing('users', ['email' => 'admin@cvora.test']);
    }

    public function test_settings_do_not_store_api_keys(): void
    {
        $admin = $this->makeUser('pro', ['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'ai_model' => 'gpt-4o-mini',
            'legal_name' => 'CVora',
            'contact_email' => 'contact@cvora.test',
            'feature_ai' => '1',
            'feature_import' => '1',
            'feature_letters' => '1',
            'feature_payments' => '1',
            'feature_exports' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseMissing('settings', ['key' => 'AI_API_KEY']);
        $this->assertDatabaseHas('settings', ['key' => 'ai_model', 'value' => 'gpt-4o-mini']);
        $this->get(route('admin.ai'))->assertDontSee('sk-');
    }
}
