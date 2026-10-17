@extends('layouts.app')
@section('document-title', 'Paramètres')
@section('content')
    <h1 class="h2">Paramètres</h1>
    <form method="POST" action="{{ route('settings.preferences') }}" class="card mt-3">
        @csrf @method('PUT')
        <div class="card-body vstack gap-3">
            <h2 class="h5">Préférences</h2>
            <div>
                <label class="form-label" for="language">Langue</label>
                <select class="form-select" id="language" name="language"><option value="fr" selected>Français</option></select>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" value="1" @checked($preferences['email_notifications'] ?? true)>
                <label class="form-check-label" for="email_notifications">Recevoir les notifications par e-mail lorsque le courriel est configuré</label>
            </div>
            <div>
                <label class="form-label" for="default_template">Modèle par défaut</label>
                <select class="form-select" id="default_template" name="default_template">
                    <option value="">Aucun</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->slug }}" @selected(($preferences['default_template'] ?? '') === $template->slug)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline-primary" type="submit" data-loading>Enregistrer les préférences</button>
        </div>
    </form>
    <form method="POST" action="{{ route('settings.password') }}" class="card mt-3 needs-validation" novalidate>
        @csrf @method('PUT')
        <div class="card-body vstack gap-3">
            <h2 class="h5">Mot de passe</h2>
            <input class="form-control" type="password" name="current_password" placeholder="Mot de passe actuel" required autocomplete="current-password" aria-label="Mot de passe actuel">
            <input class="form-control" type="password" name="password" placeholder="Nouveau mot de passe" required autocomplete="new-password" aria-label="Nouveau mot de passe">
            <input class="form-control" type="password" name="password_confirmation" placeholder="Confirmation" required autocomplete="new-password" aria-label="Confirmation du mot de passe">
            <button class="btn btn-outline-primary" type="submit" data-loading>Changer le mot de passe</button>
        </div>
    </form>
    <section class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Connexion par téléphone</h2>
            @if ($phoneAuthAvailable)
                <p class="mb-0">Le fournisseur SMS est disponible.</p>
            @else
                <p class="text-secondary mb-0">Non disponible : aucun fournisseur SMS n'est configuré. Le champ téléphone du compte est déjà prévu pour ce branchement.</p>
            @endif
        </div>
    </section>
    <section class="card border-danger mt-3">
        <div class="card-body">
            <h2 class="h5">Supprimer le compte</h2>
            <p class="small text-secondary">Les CV, lettres, imports et candidatures sont supprimés. Le compte est anonymisé. Les transactions restent sans contenu de document.</p>
            <x-confirm-form id="delete-account" title="Supprimer mon compte" :action="route('settings.destroy')" trigger="Supprimer mon compte" submit="Supprimer définitivement" trigger-class="btn btn-danger">
                <p>Les CV, lettres, imports et candidatures sont supprimés. Le compte est anonymisé.</p>
                <label class="form-label" for="delete-password">Mot de passe</label>
                <input class="form-control" id="delete-password" type="password" name="password" required autocomplete="current-password">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="confirm_delete" id="confirm_delete" value="1" required>
                    <label class="form-check-label" for="confirm_delete">Je confirme la suppression.</label>
                    <div class="invalid-feedback">Confirmez la suppression du compte.</div>
                </div>
            </x-confirm-form>
        </div>
    </section>
@endsection
