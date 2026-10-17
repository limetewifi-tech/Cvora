<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\PaymentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request, PaymentService $payments)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:180', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'accepted_terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'locale' => 'fr',
            'terms_accepted_at' => now(),
        ]);

        $parts = preg_split('/\s+/', trim($data['name']), 2) ?: [];
        $user->profile()->create([
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
            'phone' => $data['phone'] ?? null,
            'preferences' => ['email_notifications' => true, 'language' => 'fr'],
        ]);

        $free = Plan::query()->where('slug', 'gratuit')->where('is_active', true)->first();

        if ($free) {
            $payments->assignPlanWithoutPayment($user, $free);
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Votre compte CVora est prêt.');
    }
}
