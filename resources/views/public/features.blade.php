@extends('layouts.public')
@section('document-title', 'Fonctionnalités — CVora')
@section('content')
    <div class="container cvora-wide py-5">
        <h1 class="display-5 fw-semibold">Fonctionnalités</h1>
        <div class="vstack gap-3 mt-4">
            @foreach ([
                'Éditeur de CV en dix étapes, avec ajout, suppression et réordonnancement des éléments.',
                'Huit mises en page distinctes, de l\'étudiant au profil technique ou administratif.',
                'Import PDF et DOCX, relecture obligatoire, aucune exécution du fichier.',
                'Lettres de motivation liées à un CV ou à une offre.',
                'Assistant IA centralisé, propositions affichées avant application.',
                'Studio CV : score local documenté entre un CV et une offre, suggestions IA seulement après autorisation.',
                'Offres, quotas et paiement extensible, avec bac à sable séparé d\'iKeePay.',
            ] as $item)
                <p class="card mb-0"><span class="card-body">{{ $item }}</span></p>
            @endforeach
        </div>
    </div>
@endsection
