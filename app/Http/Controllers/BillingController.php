<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\Billing\PaymentService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('subscription.plan');

        return view('billing.index', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
            'subscription' => $user->subscription,
            'transactions' => $user->transactions()->latest()->limit(15)->get(),
            'driver' => config('cvora.payments.driver'),
            'sandbox' => config('cvora.payments.driver') === 'sandbox',
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

            $checkout = $payments->startCheckout($request->user(), $plan);
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
