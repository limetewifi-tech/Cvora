@extends('layouts.admin')
@section('document-title', 'Administration')
@section('content')
    <h1 class="h2">Administration</h1>
    <p class="small text-secondary">Compteurs globaux. Le contenu des CV n'est pas affiché.</p>
    <div class="row g-3 mt-1">
        @foreach (['Comptes' => $users, 'Comptes actifs' => $activeUsers, 'CV (nombre)' => $resumes, 'Lettres (nombre)' => $letters, 'Abonnements actifs' => $subscriptions, 'Paiements confirmés' => $succeeded, 'Appels IA réussis' => $aiCalls, 'Erreurs techniques' => $errorCount] as $label => $value)
            <div class="col-sm-6 col-lg-3">
                <article class="card h-100"><div class="card-body"><p class="small text-secondary mb-1">{{ $label }}</p><p class="h3 fw-semibold mb-0">{{ $value }}</p></div></article>
            </div>
        @endforeach
    </div>
@endsection
