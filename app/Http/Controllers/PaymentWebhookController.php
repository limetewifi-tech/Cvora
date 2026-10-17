<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Services\Billing\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function store(Request $request, string $provider, PaymentService $payments)
    {
        abort_unless(in_array($provider, ['sandbox', 'unipay'], true), 404);

        try {
            $transaction = $payments->handleWebhook(
                $provider,
                $request->getContent(),
                $request->header('X-Cvora-Signature'),
            );
        } catch (PaymentException $exception) {
            $status = str_contains($exception->getMessage(), 'Signature') ? 401 : 422;

            return response()->json(['message' => $exception->getMessage()], $status);
        }

        return response()->json([
            'reference' => $transaction->reference,
            'status' => $transaction->status->value,
        ]);
    }
}
