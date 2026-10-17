<?php

namespace App\Http\Middleware;

use App\Services\Billing\QuotaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDefaultPlan
{
    public function __construct(private QuotaService $quota) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $this->quota->ensureDefaultPlan($user)) {
            $request->session()->flash(
                'status',
                'L\'abonnement Gratuit est activé. Vous pouvez créer votre CV.',
            );
        }

        return $next($request);
    }
}
