@extends('layouts.app')
@section('document-title', 'Lettres de motivation')
@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h2 mb-1">Lettres de motivation</h1>
            <p class="small text-secondary mb-0">La lettre se rédige à part du CV. Une proposition de l'assistant n'est enregistrée que si vous la validez.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('letters.create') }}">Nouvelle lettre</a>
    </div>
    <div class="row g-3 mt-3">
        @foreach ($layouts as $slug => $layout)
            <div class="col-sm-6 col-lg-3">
                <article class="card h-100 overflow-hidden">
                    <a class="cvora-thumb" href="{{ route('letters.create', ['modele' => $slug]) }}">
                        <iframe title="" src="{{ route('letters.sample', $slug) }}" loading="lazy" tabindex="-1"></iframe>
                    </a>
                    <div class="card-body">
                        <h2 class="h6 mb-1">{{ $layout['name'] }}</h2>
                        <p class="small text-secondary">{{ $layout['description'] }}</p>
                        @if ($quota->allows(auth()->user(), 'cover_letters'))
                            <a class="btn btn-primary btn-sm" href="{{ route('letters.create', ['modele' => $slug]) }}">Utiliser ce modèle</a>
                        @else
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('billing.index') }}">Disponible avec une offre supérieure</a>
                        @endif
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="vstack gap-3 mt-4">
        @forelse ($letters as $letter)
            <article class="card">
                <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $letter->title }}</h2>
                        <p class="small text-secondary mb-0">{{ $letter->position }} {{ $letter->company }}</p>
                    </div>
                    <a class="btn btn-outline-primary" href="{{ route('letters.edit', $letter) }}">Modifier</a>
                </div>
            </article>
        @empty
            @include('components.empty', ['icon' => 'bi-envelope-paper', 'title' => 'Aucune lettre enregistrée.', 'action' => 'Nouvelle lettre', 'href' => route('letters.create')])
        @endforelse
    </div>
@endsection
