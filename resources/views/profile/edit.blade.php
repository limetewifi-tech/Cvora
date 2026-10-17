@extends('layouts.app')
@section('document-title', 'Profil')
@section('content')
    <h1 class="text-3xl font-semibold">Mon profil</h1>
    <p class="mt-2 text-sm text-slate-600">Ces informations préremplissent les nouveaux CV. Elles ne sont pas envoyées tant que vous ne les placez pas dans un document.</p>
    <form method="POST" action="{{ route('profile.update') }}" class="card mt-6 grid gap-4 p-6 sm:grid-cols-2">
        @csrf @method('PUT')
        <div><label class="label">Nom affiché</label><input class="field" name="name" value="{{ old('name', $user->name) }}" required></div>
        <div><label class="label">E-mail du compte</label><input class="field" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
        <div><label class="label">Téléphone du compte</label><input class="field" name="phone" value="{{ old('phone', $user->phone) }}"></div>
        <div><label class="label">Téléphone du CV</label><input class="field" name="profile_phone" value="{{ old('profile_phone', $profile?->phone) }}"></div>
        <div><label class="label">Prénom</label><input class="field" name="first_name" value="{{ old('first_name', $profile?->first_name) }}"></div>
        <div><label class="label">Nom</label><input class="field" name="last_name" value="{{ old('last_name', $profile?->last_name) }}"></div>
        <div class="sm:col-span-2"><label class="label">Titre</label><input class="field" name="headline" value="{{ old('headline', $profile?->headline) }}"></div>
        <div><label class="label">Ville</label><input class="field" name="city" value="{{ old('city', $profile?->city) }}"></div>
        <div><label class="label">Pays</label><input class="field" name="country" value="{{ old('country', $profile?->country) }}"></div>
        <div class="sm:col-span-2"><label class="label">Adresse</label><input class="field" name="address" value="{{ old('address', $profile?->address) }}"></div>
        <div><label class="label">LinkedIn</label><input class="field" name="linkedin" value="{{ old('linkedin', $profile?->linkedin) }}"></div>
        <div><label class="label">Site</label><input class="field" name="website" value="{{ old('website', $profile?->website) }}"></div>
        <div class="sm:col-span-2"><label class="label">Résumé</label><textarea class="field min-h-28" name="summary">{{ old('summary', $profile?->summary) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </form>
@endsection
