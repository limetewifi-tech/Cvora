<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->load('profile');

        return view('profile.edit', ['user' => $user, 'profile' => $user->profile]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:32'],
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'headline' => ['nullable', 'string', 'max:160'],
            'profile_phone' => ['nullable', 'string', 'max:32'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:180'],
            'linkedin' => ['nullable', 'string', 'max:180'],
            'website' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:4000'],
        ]);

        $emailChanged = $user->email !== $data['email'];
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($emailChanged && config('cvora.must_verify_email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && config('cvora.must_verify_email')) {
            $user->sendEmailVerificationNotification();
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'headline' => $data['headline'] ?? null,
            'phone' => $data['profile_phone'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'address' => $data['address'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'website' => $data['website'] ?? null,
            'summary' => $data['summary'] ?? null,
        ]);

        return back()->with('success', 'Profil mis à jour.');
    }
}
