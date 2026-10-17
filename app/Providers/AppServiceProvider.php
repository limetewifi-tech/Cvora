<?php

namespace App\Providers;

use App\Services\Ai\AiProvider;
use App\Services\Ai\AiRouter;
use App\Services\Auth\PhoneAuthenticator;
use App\Services\Auth\UnconfiguredPhoneAuthenticator;
use App\Services\Billing\IkeepayGateway;
use App\Services\Billing\PaymentGateway;
use App\Services\Billing\SandboxGateway;
use App\Services\Resume\NullOcrExtractor;
use App\Services\Resume\OcrExtractor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiProvider::class, function ($app) {
            $router = $app->make(AiRouter::class);

            if ($app->environment('production') && $router->driverName() === 'fake') {
                throw new \RuntimeException('AI_DRIVER=fake est interdit en production.');
            }

            return $router;
        });

        $this->app->singleton(PaymentGateway::class, function ($app) {
            return match (config('cvora.payments.driver')) {
                'ikeepay' => $app->make(IkeepayGateway::class),
                default => new SandboxGateway,
            };
        });

        $this->app->singleton(OcrExtractor::class, fn () => new NullOcrExtractor);
        $this->app->singleton(PhoneAuthenticator::class, fn () => new UnconfiguredPhoneAuthenticator);
    }

    public function boot(): void
    {
        \Carbon\Carbon::setLocale(config('app.locale'));
        Paginator::useBootstrapFive();

        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip())));
        RateLimiter::for('imports', fn (Request $request) => Limit::perMinute(8)->by((string) ($request->user()?->id ?: $request->ip())));
        RateLimiter::for('sensitive', fn (Request $request) => Limit::perMinute(5)->by((string) $request->ip()));
        RateLimiter::for('webhooks', fn (Request $request) => Limit::perMinute(60)->by((string) $request->ip()));
    }
}
