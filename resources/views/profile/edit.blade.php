@extends('layouts.app')
@section('document-title', 'Profil')
@section('content')
    <h1 class="h2">Mon profil</h1>
    <p class="small text-secondary">Ces informations préremplissent les nouveaux CV. Elles ne sont pas envoyées tant que vous ne les placez pas dans un document.</p>
    <form method="POST" action="{{ route('profile.update') }}" class="card mt-3 needs-validation" novalidate>
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label">Nom affiché</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"></div>
                <div class="col-sm-6"><label class="form-label">E-mail du compte</label><input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></div>
                <div class="col-sm-6"><label class="form-label">Téléphone du compte</label><input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel"></div>
                <div class="col-sm-6"><label class="form-label">Téléphone du CV</label><input class="form-control" name="profile_phone" value="{{ old('profile_phone', $profile?->phone) }}"></div>
                <div class="col-sm-6"><label class="form-label">Prénom</label><input class="form-control" name="first_name" value="{{ old('first_name', $profile?->first_name) }}"></div>
                <div class="col-sm-6"><label class="form-label">Nom</label><input class="form-control" name="last_name" value="{{ old('last_name', $profile?->last_name) }}"></div>
                <div class="col-12"><label class="form-label">Titre</label><input class="form-control" name="headline" value="{{ old('headline', $profile?->headline) }}"></div>
                <div class="col-sm-6"><label class="form-label">Ville</label><input class="form-control" name="city" value="{{ old('city', $profile?->city) }}"></div>
                <div class="col-sm-6"><label class="form-label">Pays</label><input class="form-control" name="country" value="{{ old('country', $profile?->country) }}"></div>
                <div class="col-12"><label class="form-label">Adresse</label><input class="form-control" name="address" value="{{ old('address', $profile?->address) }}"></div>
                <div class="col-sm-6"><label class="form-label">LinkedIn</label><input class="form-control" name="linkedin" value="{{ old('linkedin', $profile?->linkedin) }}"></div>
                <div class="col-sm-6"><label class="form-label">Site</label><input class="form-control" name="website" value="{{ old('website', $profile?->website) }}"></div>
                <div class="col-12"><label class="form-label">Résumé</label><textarea class="form-control cvora-textarea" name="summary">{{ old('summary', $profile?->summary) }}</textarea></div>
                <div class="col-12"><button class="btn btn-primary" type="submit" data-loading>Enregistrer</button></div>
            </div>
        </div>
    </form>
@endsection
