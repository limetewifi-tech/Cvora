<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Billing\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_sandbox_confirmation_activates_the_plan_and_is_not_a_real_charge(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('billing.checkout', 'premium'))
            ->assertRedirect();

        $transaction = $user->transactions()->first();
        $this->assertSame(TransactionStatus::Pending, $transaction->status);
        $this->assertSame('sandbox', $transaction->provider);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);

        $this->get(route('billing.sandbox', $transaction))
            ->assertOk()
            ->assertSee('bac à sable');

        $this->post(route('billing.sandbox.confirm', $transaction))
            ->assertRedirect(route('billing.index'));

        $user->refresh();
        $this->assertSame('premium', $user->subscription->plan->slug);
        $this->assertSame(TransactionStatus::Succeeded, $transaction->fresh()->status);
        $this->assertSame(100, $user->subscription->credits_remaining);
    }

    public function test_webhook_is_idempotent_and_rejects_a_bad_signature_or_amount(): void
    {
        $user = $this->makeUser();
        $plan = Plan::query()->where('slug', 'pro')->firstOrFail();
        $transaction = $this->pending($user, $plan, 'sandbox');
        $amount = number_format((float) $transaction->amount, 2, '.', '');

        $this->postWebhook($transaction, $this->payload($transaction, 'succeeded', $amount), 'sig-invalide')
            ->assertUnauthorized();
        $this->assertSame(TransactionStatus::Pending, $transaction->fresh()->status);

        $this->postWebhook($transaction, $this->payload($transaction, 'succeeded', '1.00'))
            ->assertStatus(422);
        $this->assertSame(TransactionStatus::Pending, $transaction->fresh()->status);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);

        $this->postWebhook($transaction, $this->payload($transaction, 'succeeded', $amount))->assertOk();
        $this->postWebhook($transaction, $this->payload($transaction, 'succeeded', $amount))->assertOk();

        $this->assertSame(1, Transaction::query()->where('status', TransactionStatus::Succeeded)->count());
        $this->assertSame('pro', $user->fresh()->subscription->plan->slug);
        $this->assertSame(400, $user->subscription()->first()->credits_remaining);
    }

    public function test_a_failed_webhook_does_not_activate_the_plan(): void
    {
        $user = $this->makeUser();
        $plan = Plan::query()->where('slug', 'premium')->firstOrFail();
        $transaction = $this->pending($user, $plan, 'sandbox');
        $amount = number_format((float) $transaction->amount, 2, '.', '');

        $this->postWebhook($transaction, $this->payload($transaction, 'failed', $amount))->assertOk();

        $this->assertSame(TransactionStatus::Failed, $transaction->fresh()->status);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);
    }

    public function test_sandbox_confirmation_is_unavailable_when_the_driver_is_unipay(): void
    {
        config(['cvora.payments.driver' => 'unipay']);
        $user = $this->makeUser();
        $transaction = $this->pending($user, Plan::query()->where('slug', 'premium')->firstOrFail(), 'sandbox');

        $this->actingAs($user)->post(route('billing.sandbox.confirm', $transaction))->assertNotFound();
        $this->assertSame(TransactionStatus::Pending, $transaction->fresh()->status);
    }

    public function test_unipay_checkout_stays_pending_until_a_signed_webhook(): void
    {
        config([
            'cvora.payments.driver' => 'unipay',
            'cvora.payments.unipay.api_url' => 'https://pay.example.test',
            'cvora.payments.unipay.api_key' => 'test-key',
            'cvora.payments.unipay.merchant_id' => 'merchant-1',
        ]);
        $this->app->forgetInstance(PaymentGateway::class);

        Http::fake([
            'https://pay.example.test/*' => Http::response([
                'checkout_url' => 'https://pay.example.test/checkout/abc',
                'provider_reference' => 'up_abc',
            ], 200),
        ]);

        $user = $this->makeUser();

        $this->actingAs($user)->post(route('billing.checkout', 'premium'))
            ->assertRedirect('https://pay.example.test/checkout/abc');

        $transaction = $user->transactions()->first();
        $this->assertSame(TransactionStatus::Pending, $transaction->status);
        $this->assertSame('unipay', $transaction->provider);
        $this->assertSame('up_abc', $transaction->provider_reference);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);
        Http::assertSentCount(1);
    }

    private function pending(User $user, Plan $plan, string $provider): Transaction
    {
        return $user->transactions()->create([
            'plan_id' => $plan->id,
            'reference' => 'cvora_'.Str::lower(Str::ulid()),
            'provider' => $provider,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => TransactionStatus::Pending,
        ]);
    }

    private function payload(Transaction $transaction, string $status, string $amount): array
    {
        return [
            'reference' => $transaction->reference,
            'provider_reference' => 'pay_1',
            'status' => $status,
            'amount' => $amount,
            'currency' => $transaction->currency,
        ];
    }

    private function postWebhook(Transaction $transaction, array $payload, ?string $signature = null)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $signature ??= hash_hmac('sha256', $body, (string) config('cvora.payments.webhook_secret'));

        return $this->call('POST', route('payments.webhook', $transaction->provider), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CVORA_SIGNATURE' => $signature,
        ], $body);
    }
}
