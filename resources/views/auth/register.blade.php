@extends('layouts.auth')
@section('document-title', 'Inscription')
@section('content')
    <h1 class="text-2xl font-semibold">Créer mon compte</h1>
    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf
        <div><label class="label" for="name">Nom complet</label><input class="field" id="name" name="name" value="{{ old('name') }}" required></div>
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
        <div><label class="label" for="phone">Téléphone <span class="font-normal text-slate-500">(facultatif, pour une future connexion)</span></label><input class="field" id="phone" name="phone" value="{{ old('phone') }}"></div>
        <div><label class="label" for="password">Mot de passe</label><input class="field" id="password" type="password" name="password" required></div>
        <div><label class="label" for="password_confirmation">Confirmation</label><input class="field" id="password_confirmation" type="password" name="password_confirmation" required></div>
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="accepted_terms" value="1" required> J'accepte les <a class="font-semibold text-brand-700" href="{{ route('legal.terms') }}">conditions</a> et la <a class="font-semibold text-brand-700" href="{{ route('legal.privacy') }}">confidentialité</a>.</label>
        <button class="btn btn-primary w-full" type="submit">Créer mon CV gratuitement</button>
    </form>
@endsection
