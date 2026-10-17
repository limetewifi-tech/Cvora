@extends('layouts.auth')
@section('document-title', 'Nouveau mot de passe')
@section('content')
    <h1 class="text-2xl font-semibold">Nouveau mot de passe</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" value="{{ old('email', $email) }}" required></div>
        <div><label class="label" for="password">Mot de passe</label><input class="field" id="password" type="password" name="password" required></div>
        <div><label class="label" for="password_confirmation">Confirmation</label><input class="field" id="password_confirmation" type="password" name="password_confirmation" required></div>
        <button class="btn btn-primary w-full" type="submit">Enregistrer</button>
    </form>
@endsection
