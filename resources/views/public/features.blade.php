@extends('layouts.public')
@section('document-title', 'Fonctionnalités — CVora')
@section('content')
    <div class="mx-auto max-w-4xl px-4 py-14">
        <h1 class="text-4xl font-semibold">Fonctionnalités</h1>
        <div class="mt-8 space-y-4">
            @foreach ([
                'Éditeur de CV en dix étapes, avec ajout, suppression et réordonnancement des éléments.',
                'Huit mises en page distinctes, de l\'étudiant au profil technique ou administratif.',
                'Import PDF et DOCX, relecture obligatoire, aucune exécution du fichier.',
                'Lettres de motivation liées à un CV ou à une offre.',
                'Assistant IA centralisé, propositions affichées avant application.',
                'Offres, quotas et paiement extensible, avec bac à sable séparé d\'UniPay.',
            ] as $item)
                <p class="card p-5 text-slate-700">{{ $item }}</p>
            @endforeach
        </div>
    </div>
@endsection
