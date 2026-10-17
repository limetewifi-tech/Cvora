<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Throwable;

abstract class Controller
{
    use AuthorizesRequests;

    protected function fail(Request $request, Throwable $exception, ?string $redirect = null)
    {
        $status = str_contains($exception->getMessage(), 'Rechargez') ? 409 : 422;

        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], $status);
        }

        $response = $redirect ? redirect($redirect) : back();

        return $response->withInput()->with('error', $exception->getMessage());
    }

    protected function guard(Request $request, callable $callback, ?string $redirect = null)
    {
        try {
            return $callback();
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception, $redirect);
        }
    }
}
