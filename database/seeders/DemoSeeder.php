<?php

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local') || ! config('cvora.seed_demo')) {
            return;
        }

        $this->account('admin@cvora.test', 'Admin CVora', UserRole::Admin, 'pro');
        $this->account('demo@cvora.test', 'Amina Kabila', UserRole::User, 'gratuit');
    }

    private function account(string $email, string $name, UserRole $role, string $planSlug): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'Password123!',
                'role' => $role,
                'locale' => 'fr',
                'is_active' => true,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ],
        );

        $parts = preg_split('/\s+/', $name, 2) ?: [];
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
                'city' => 'Kinshasa',
                'country' => 'République démocratique du Congo',
                'headline' => $role === UserRole::Admin ? 'Administration' : 'Exemple local',
                'preferences' => ['email_notifications' => true, 'language' => 'fr'],
            ],
        );

        $plan = Plan::query()->where('slug', $planSlug)->firstOrFail();
        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Active,
                'starts_at' => now(),
                'ends_at' => $plan->isFree() ? null : now()->addMonth(),
                'credits_remaining' => (int) $plan->ai_credits,
                'credits_reset_at' => now()->addMonth(),
            ],
        );
    }
}
