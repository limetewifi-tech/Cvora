<?php

namespace App\Http\Controllers;

use App\Models\ResumeTemplate;
use App\Services\Account\AccountDeleter;
use App\Services\Auth\PhoneAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit(Request $request, PhoneAuthenticator $phone)
    {
        $user = $request->user();
        $user->load('profile');

        return view('settings.edit', [
            'user' => $user,
            'preferences' => $user->profile?->preferences ?? [],
            'templates' => ResumeTemplate::available(),
            'phoneAuthAvailable' => $phone->available(),
        ]);
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Mot de passe modifié.');
    }

    public function preferences(Request $request)
    {
        $data = $request->validate([
            'language' => ['required', 'in:fr'],
            'email_notifications' => ['nullable', 'boolean'],
            'default_template' => ['nullable', 'string', 'max:80'],
        ]);

        $profile = $request->user()->profile()->firstOrCreate(['user_id' => $request->user()->id]);
        $preferences = $profile->preferences ?? [];
        $preferences['language'] = $data['language'];
        $preferences['email_notifications'] = $request->boolean('email_notifications');
        $preferences['default_template'] = $data['default_template'] ?? null;
        $profile->forceFill(['preferences' => $preferences])->save();
        $request->user()->forceFill(['locale' => $data['language']])->save();

        return back()->with('success', 'Préférences enregistrées.');
    }

    public function destroy(Request $request, AccountDeleter $deleter)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirm_delete' => ['accepted'],
        ]);

        $user = $request->user();

        try {
            $deleter->delete($user);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Votre compte a été anonymisé et vos documents ont été supprimés. Les transactions sont conservées sans contenu de CV.');
    }
}
