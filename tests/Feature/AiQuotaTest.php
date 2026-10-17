<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use Tests\CreatesCvoraWorld;
use Tests\TestCase;

class AiQuotaTest extends TestCase
{
    use CreatesCvoraWorld;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCatalog();
    }

    public function test_ai_credit_is_consumed_and_a_proposal_does_not_change_the_resume(): void
    {
        $user = $this->makeUser();
        $user->subscription->update(['credits_remaining' => 1]);
        $resume = $this->makeResume($user, ['summary' => 'Texte original du candidat.']);

        $this->actingAs($user)->postJson(route('assistant.summary'), [
            'text' => 'Texte original du candidat.',
            'tone' => 'professionnel',
        ])->assertOk()
            ->assertJsonPath('data.proposal', 'Texte original du candidat.')
            ->assertJsonPath('credits_remaining', 0);

        $this->assertSame('Texte original du candidat.', $resume->fresh()->summary);
        $this->assertSame(0, $user->subscription()->first()->credits_remaining);

        $this->postJson(route('assistant.summary'), [
            'text' => 'Texte original du candidat.',
        ])->assertStatus(422)
            ->assertJsonPath('successful', false);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $user->id,
            'status' => 'denied',
        ]);
        $this->assertSame(1, AiUsageLog::query()->where('status', 'success')->count());
    }

    public function test_job_adaptation_is_reserved_to_the_pro_plan(): void
    {
        $user = $this->makeUser('premium');
        $resume = $this->makeResume($user);
        $offer = $user->jobOffers()->create([
            'title' => 'Analyste',
            'company' => 'Banque exemple',
            'description' => 'Excel, reporting',
        ]);

        $this->actingAs($user)->postJson(route('assistant.analyze'), [
            'job_offer_id' => $offer->id,
            'resume_id' => $resume->id,
            'authorize_resume' => '1',
        ])->assertStatus(422);

        $this->assertNull($offer->fresh()->analysis);
        $this->assertSame(100, $user->subscription()->first()->credits_remaining);
    }

    public function test_a_balance_without_a_period_receives_the_plan_allowance_once(): void
    {
        $user = $this->makeUser();
        $user->subscription->forceFill([
            'credits_remaining' => 0,
            'credits_reset_at' => null,
        ])->save();

        $this->actingAs($user)->get(route('assistant.index'))
            ->assertOk()
            ->assertSee('Crédits restants : 5')
            ->assertSee('sur 5 pour cette période');

        $fresh = $user->fresh()->subscription;
        $this->assertSame(5, (int) $fresh->credits_remaining);
        $this->assertNotNull($fresh->credits_reset_at);

        $fresh->forceFill(['credits_remaining' => 2])->save();

        $this->get(route('assistant.index'))->assertOk()->assertSee('Crédits restants : 2');
        $this->assertSame(2, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_a_spent_balance_stays_empty_until_the_period_ends(): void
    {
        $user = $this->makeUser();
        $user->subscription->forceFill([
            'credits_remaining' => 0,
            'credits_reset_at' => now()->addDays(10),
        ])->save();

        $this->actingAs($user)->get(route('assistant.index'))
            ->assertOk()
            ->assertSee('Crédits restants : 0')
            ->assertSee('sur 5 pour cette période')
            ->assertSee('Les crédits de cette période sont utilisés');

        $this->assertSame(0, (int) $user->fresh()->subscription->credits_remaining);
    }

    public function test_ai_routes_are_rate_limited(): void
    {
        $route = app('router')->getRoutes()->getByName('assistant.summary');

        $this->assertContains('throttle:ai', $route->gatherMiddleware());
    }
}
