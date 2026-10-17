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

    public function test_sandbox_confirmation_is_unavailable_when_the_driver_is_ikeepay(): void
    {
        config(['cvora.payments.driver' => 'ikeepay']);
        $user = $this->makeUser();
        $transaction = $this->pending($user, Plan::query()->where('slug', 'premium')->firstOrFail(), 'sandbox');

        $this->actingAs($user)->post(route('billing.sandbox.confirm', $transaction))->assertNotFound();
        $this->assertSame(TransactionStatus::Pending, $transaction->fresh()->status);
    }

    public function test_ikeepay_checkout_stays_pending_until_a_verified_completion(): void
    {
        $this->useIkeepay();

        Http::fake([
            'https://api.ikeepay.test/h2h-payin' => Http::response([
                'payment_link' => 'https://pay.ikeepay.test/checkout/abc',
                'provider_reference' => 'IKP-H2H-TEST',
            ], 200),
        ]);

        $user = $this->makeUser();

        $this->actingAs($user)->post(route('billing.checkout', 'premium'), $this->ikeepayDetails())
            ->assertRedirect('https://pay.ikeepay.test/checkout/abc');

        $transaction = $user->transactions()->first();
        $this->assertSame(TransactionStatus::Pending, $transaction->status);
        $this->assertSame('ikeepay', $transaction->provider);
        $this->assertSame('IKP-H2H-TEST', $transaction->provider_reference);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.ikeepay.test/h2h-payin'
                && $request->hasHeader('x-api-key', 'test-key')
                && ! $request->hasHeader('Authorization')
                && $request['phoneNumber'] === '243810000000'
                && $request['country'] === 'CD'
                && $request['operator'] === 'AIRTEL'
                && $request['currency'] === 'CDF'
                && $request['amount'] === 15000;
        });
        Http::assertSentCount(1);
    }

    public function test_ikeepay_webhook_activates_only_after_a_matching_verification(): void
    {
        $this->useIkeepay();
        $user = $this->makeUser();
        $plan = Plan::query()->where('slug', 'premium')->firstOrFail();
        $transaction = $this->pending($user, $plan, 'ikeepay');

        Http::fake([
            'https://api.ikeepay.test/h2h-verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'external_reference' => $transaction->reference,
                    'provider_reference' => 'IKP-H2H-1',
                    'amount' => 15000,
                    'currency' => 'CDF',
                    'status' => 'completed',
                    'type' => 'payin',
                    'phone_number' => '243810000000',
                ],
            ]),
        ]);

        $this->postIkeepayWebhook($transaction, 1)->assertOk();
        $this->postIkeepayWebhook($transaction, 1)->assertOk();

        $fresh = $transaction->fresh();
        $this->assertSame(TransactionStatus::Succeeded, $fresh->status);
        $this->assertSame('premium', $user->fresh()->subscription->plan->slug);
        $this->assertSame(1, Transaction::query()->where('status', TransactionStatus::Succeeded)->count());
        $this->assertStringNotContainsString('243810000000', json_encode($fresh->payload));
        $this->assertStringNotContainsString('test-key', json_encode($fresh->payload));
        Http::assertSentCount(2);
    }

    public function test_ikeepay_pending_or_failed_verification_does_not_activate_the_plan(): void
    {
        $this->useIkeepay();
        $user = $this->makeUser();
        $plan = Plan::query()->where('slug', 'premium')->firstOrFail();
        $pending = $this->pending($user, $plan, 'ikeepay');
        $failed = $this->pending($user, $plan, 'ikeepay');

        Http::fake([
            'https://api.ikeepay.test/h2h-verify/'.$pending->reference => Http::response([
                'status' => 'success',
                'data' => [
                    'amount' => 15000,
                    'currency' => 'CDF',
                    'status' => 'pending',
                    'type' => 'payin',
                ],
            ]),
            'https://api.ikeepay.test/h2h-verify/'.$failed->reference => Http::response([
                'status' => 'success',
                'data' => [
                    'amount' => 15000,
                    'currency' => 'CDF',
                    'status' => 'failed',
                    'type' => 'payin',
                ],
            ]),
        ]);

        $this->postIkeepayWebhook($pending)->assertOk();
        $this->postIkeepayWebhook($failed)->assertOk();

        $this->assertSame(TransactionStatus::Pending, $pending->fresh()->status);
        $this->assertSame(TransactionStatus::Failed, $failed->fresh()->status);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);
    }

    public function test_ikeepay_amount_mismatch_stays_pending(): void
    {
        $this->useIkeepay();
        $user = $this->makeUser();
        $transaction = $this->pending($user, Plan::query()->where('slug', 'premium')->firstOrFail(), 'ikeepay');

        Http::fake([
            'https://api.ikeepay.test/h2h-verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'provider_reference' => 'IKP-H2H-2',
                    'amount' => 1,
                    'currency' => 'CDF',
                    'status' => 'completed',
                    'type' => 'payin',
                ],
            ]),
        ]);

        $this->postIkeepayWebhook($transaction)->assertStatus(422);
        $this->assertSame(TransactionStatus::Pending, $transaction->fresh()->status);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);
    }

    public function test_ikeepay_checkout_without_a_key_does_not_call_the_provider(): void
    {
        config([
            'cvora.payments.driver' => 'ikeepay',
            'cvora.payments.ikeepay.api_url' => 'https://api.ikeepay.test',
            'cvora.payments.ikeepay.api_key' => '',
        ]);
        $this->app->forgetInstance(PaymentGateway::class);
        Http::preventStrayRequests();

        $user = $this->makeUser();

        $this->actingAs($user)->from(route('billing.index'))
            ->post(route('billing.checkout', 'premium'), $this->ikeepayDetails())
            ->assertRedirect(route('billing.index'))
            ->assertSessionHas('error');

        $transaction = $user->transactions()->first();
        $this->assertSame(TransactionStatus::Failed, $transaction->status);
        $this->assertSame('gratuit', $user->fresh()->subscription->plan->slug);
        Http::assertNothingSent();
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

    private function useIkeepay(): void
    {
        config([
            'cvora.payments.driver' => 'ikeepay',
            'cvora.payments.ikeepay.api_url' => 'https://api.ikeepay.test',
            'cvora.payments.ikeepay.api_key' => 'test-key',
        ]);
        $this->app->forgetInstance(PaymentGateway::class);
        Http::preventStrayRequests();
    }

    /**
     * @return array{phone_number: string, country: string, operator: string}
     */
    private function ikeepayDetails(): array
    {
        return [
            'phone_number' => '243810000000',
            'country' => 'CD',
            'operator' => 'AIRTEL',
        ];
    }

    private function postIkeepayWebhook(Transaction $transaction, int|float $amount = 15000)
    {
        $body = json_encode([
            'event' => 'transaction.updated',
            'data' => [
                'type' => 'payin',
                'external_reference' => $transaction->reference,
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $transaction->currency,
                'phone_number' => '243810000000',
            ],
        ], JSON_UNESCAPED_UNICODE);

        return $this->call('POST', route('payments.webhook', 'ikeepay'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);
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
