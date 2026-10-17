<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\Billing\PaymentService;
use App\Services\Billing\QuotaService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('subscription.plan');

        if ($user->subscription) {
            app(QuotaService::class)->refreshCredits($user->subscription);
            $user->unsetRelation('subscription');
            $user->load('subscription.plan');
        }

        return view('billing.index', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
            'subscription' => $user->subscription,
            'transactions' => $user->transactions()->latest()->limit(15)->get(),
            'driver' => config('cvora.payments.driver'),
            'sandbox' => config('cvora.payments.driver') === 'sandbox',
            'ikeepay' => config('cvora.payments.driver') === 'ikeepay',
            'ikeepayReady' => filled(config('cvora.payments.ikeepay.api_key')) && filled(config('cvora.payments.ikeepay.api_url')),
        ]);
    }

    public function checkout(Request $request, Plan $plan, PaymentService $payments)
    {
        abort_unless($plan->is_active, 404);

        try {
            if ($plan->isFree()) {
                $payments->assignPlanWithoutPayment($request->user(), $plan);

                return redirect()->route('billing.index')->with('success', 'L\'offre gratuite est active. Aucun paiement n\'a été effectué.');
            }

            $details = [];

            if (config('cvora.payments.driver') === 'ikeepay') {
                $details = $request->validate([
                    'phone_number' => ['required', 'regex:/^[0-9]{8,15}$/'],
                    'country' => ['required', 'regex:/^[A-Za-z]{2}$/'],
                    'operator' => ['required', 'regex:/^[A-Za-z0-9_]{2,20}$/'],
                ], [
                    'phone_number.required' => 'Indiquez le numéro Mobile Money.',
                    'phone_number.regex' => 'Le numéro doit contenir entre 8 et 15 chiffres.',
                    'country.required' => 'Indiquez le code pays.',
                    'country.regex' => 'Le pays doit être un code de deux lettres.',
                    'operator.required' => 'Indiquez l\'opérateur.',
                    'operator.regex' => 'L\'opérateur indiqué n\'est pas accepté.',
                ]);
            }

            $checkout = $payments->startCheckout($request->user(), $plan, $details);
        } catch (PaymentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (! empty($checkout['redirect_url'])) {
            return config('cvora.payments.driver') === 'sandbox'
                ? redirect($checkout['redirect_url'])
                : redirect()->away($checkout['redirect_url']);
        }

        return redirect()->route('billing.index')->with('success', 'Le paiement a été initié. L\'offre sera activée seulement après confirmation du prestataire.');
    }

    public function sandbox(Request $request, Transaction $transaction)
    {
        $this->own($request, $transaction);
        abort_unless(config('cvora.payments.driver') === 'sandbox', 404);
        $transaction->load('plan');

        return view('billing.sandbox', ['transaction' => $transaction]);
    }

    public function confirmSandbox(Request $request, Transaction $transaction, PaymentService $payments)
    {
        $this->own($request, $transaction);

        try {
            $payments->confirmSandbox($transaction, $request->user());
        } catch (PaymentException $exception) {
            abort(404, $exception->getMessage());
        }

        return redirect()->route('billing.index')->with('success', 'Paiement bac à sable confirmé. Ce n\'est pas un encaissement réel.');
    }

    public function failSandbox(Request $request, Transaction $transaction, PaymentService $payments)
    {
        $this->own($request, $transaction);

        try {
            $payments->failSandbox($transaction, $request->user());
        } catch (PaymentException $exception) {
            abort(404);
        }

        return redirect()->route('billing.index')->with('error', 'Le paiement bac à sable a été marqué comme échoué. L\'offre n\'a pas changé.');
    }

    private function own(Request $request, Transaction $transaction): void
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);
    }
}
