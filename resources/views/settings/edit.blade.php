@extends('layouts.app')
@section('document-title', 'Paramètres')
@section('content')
    <h1 class="text-3xl font-semibold">Paramètres</h1>
    <form method="POST" action="{{ route('settings.preferences') }}" class="card mt-6 space-y-3 p-5">
        @csrf @method('PUT')
        <h2 class="font-semibold">Préférences</h2>
        <label class="label">Langue</label><select class="field" name="language"><option value="fr" selected>Français</option></select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="email_notifications" value="1" @checked($preferences['email_notifications'] ?? true)> Recevoir les notifications par e-mail lorsque le courriel est configuré</label>
        <label class="label">Modèle par défaut</label><select class="field" name="default_template"><option value="">Aucun</option>@foreach ($templates as $template)<option value="{{ $template->slug }}" @selected(($preferences['default_template'] ?? '') === $template->slug)>{{ $template->name }}</option>@endforeach</select>
        <button class="btn btn-secondary" type="submit">Enregistrer les préférences</button>
    </form>
    <form method="POST" action="{{ route('settings.password') }}" class="card mt-4 space-y-3 p-5">
        @csrf @method('PUT')
        <h2 class="font-semibold">Mot de passe</h2>
        <input class="field" type="password" name="current_password" placeholder="Mot de passe actuel" required>
        <input class="field" type="password" name="password" placeholder="Nouveau mot de passe" required>
        <input class="field" type="password" name="password_confirmation" placeholder="Confirmation" required>
        <button class="btn btn-secondary" type="submit">Changer le mot de passe</button>
    </form>
    <section class="card mt-4 p-5 text-sm">
        <h2 class="font-semibold">Connexion par téléphone</h2>
        @if ($phoneAuthAvailable)
            <p class="mt-2">Le fournisseur SMS est disponible.</p>
        @else
            <p class="mt-2 text-slate-600">Non disponible : aucun fournisseur SMS n'est configuré. Le champ téléphone du compte est déjà prévu pour ce branchement.</p>
        @endif
    </section>
    <form method="POST" action="{{ route('settings.destroy') }}" class="card mt-4 space-y-3 border-red-200 p-5">
        @csrf @method('DELETE')
        <h2 class="font-semibold">Supprimer le compte</h2>
        <p class="text-sm text-slate-600">Les CV, lettres, imports et candidatures sont supprimés. Le compte est anonymisé. Les transactions restent sans contenu de document.</p>
        <input class="field" type="password" name="password" placeholder="Mot de passe" required>
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirm_delete" value="1" required> Je confirme la suppression.</label>
        <button class="btn btn-primary" type="submit">Supprimer mon compte</button>
    </form>
@endsection
