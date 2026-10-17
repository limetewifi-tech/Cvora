<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_registration_creates_a_free_account(): void
    {
        $response = $this->post('/inscription', [
            'name' => 'Jean Mukendi',
            'email' => 'jean@exemple.cd',
            'phone' => '+243810000111',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'accepted_terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'jean@exemple.cd')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role->value);
        $this->assertSame('Jean', $user->profile->first_name);
        $this->assertSame('gratuit', $user->subscription->plan->slug);
        $this->assertNotNull($user->terms_accepted_at);
    }

    public function test_login_and_logout(): void
    {
        $user = $this->makeUser();

        $this->post('/connexion', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post('/deconnexion')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/connexion', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Nouveau123!',
            'password_confirmation' => 'Nouveau123!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Nouveau123!', $user->fresh()->password));
    }

    public function test_unknown_email_gets_the_same_reset_message(): void
    {
        $this->post(route('password.email'), ['email' => 'inconnu@exemple.cd'])
            ->assertSessionHas('status');
    }

    public function test_email_verification_is_enforced_only_when_enabled(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        config(['cvora.must_verify_email' => true]);
        Notification::fake();
        $user->sendEmailVerificationNotification();
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_private_routes_require_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('cvs.index'))->assertRedirect(route('login'));
    }
}
