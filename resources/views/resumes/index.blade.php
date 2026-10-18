@extends('layouts.app')
@section('document-title', 'Mes CV')
@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h1 class="h2 mb-0">Mes CV</h1>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('start.index', ['objectif' => 'creer']) }}">Créer un CV</a>
            <a class="btn btn-outline-primary" href="{{ route('imports.create') }}">Importer</a>
        </div>
    </div>
    <div class="vstack gap-3 mt-4">
        @forelse ($resumes as $resume)
            <article class="card">
                <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $resume->title }}</h2>
                        <p class="small text-secondary mb-0">{{ $resume->template?->name }} · modifié {{ $resume->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 1]) }}">Étape par étape</a>
                        <a class="btn btn-outline-primary" href="{{ route('cvs.edit', $resume) }}">Éditeur</a>
                        <a class="btn btn-outline-primary" href="{{ route('cvs.preview', $resume) }}" target="_blank" rel="noopener">Aperçu</a>
                        <form method="POST" action="{{ route('cvs.duplicate', $resume) }}">@csrf<button class="btn btn-outline-secondary" type="submit">Dupliquer</button></form>
                    </div>
                </div>
            </article>
        @empty
            @include('components.empty', ['icon' => 'bi-file-earmark-plus', 'title' => 'Vous n\'avez pas encore de CV.', 'action' => 'Créer un CV', 'href' => route('cvs.create')])
        @endforelse
    </div>
@endsection
