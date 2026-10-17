@extends('layouts.admin')
@section('document-title', 'Administration')
@section('content')
    <h1 class="text-3xl font-semibold">Administration</h1>
    <p class="mt-2 text-sm text-slate-600">Compteurs globaux. Le contenu des CV n'est pas affiché.</p>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach (['Comptes' => $users, 'Comptes actifs' => $activeUsers, 'CV (nombre)' => $resumes, 'Lettres (nombre)' => $letters, 'Abonnements actifs' => $subscriptions, 'Paiements confirmés' => $succeeded, 'Appels IA réussis' => $aiCalls, 'Erreurs techniques' => $errorCount] as $label => $value)
            <article class="card p-4"><p class="text-sm text-slate-500">{{ $label }}</p><p class="text-2xl font-semibold">{{ $value }}</p></article>
        @endforeach
    </div>
@endsection
