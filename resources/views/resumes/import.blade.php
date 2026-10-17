@extends('layouts.app')
@section('document-title', 'Importer un CV')
@section('content')
    <h1 class="h2">Importer un CV</h1>
    <p class="cvora-narrow text-secondary">PDF texte ou Word .docx, {{ $maxKb }} Ko maximum. Le fichier est analysé, jamais exécuté. Vous confirmez les champs avant l'enregistrement.</p>
    @if (! $enabled)
        <div class="alert alert-warning mt-4">{{ $disabledMessage }}</div>
    @else
        <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="card cvora-narrow mt-4 needs-validation" novalidate>
            @csrf
            <div class="card-body vstack gap-3">
                <div>
                    <label class="form-label" for="file">Fichier</label>
                    <input class="form-control" id="file" type="file" name="file" accept=".pdf,.docx,application/pdf" required>
                    <div class="invalid-feedback">Choisissez un PDF ou un document Word.</div>
                </div>
                <button class="btn btn-primary" type="submit" data-loading>Analyser le fichier</button>
            </div>
        </form>
    @endif
    <h2 class="h5 mt-4">Imports récents</h2>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($imports as $import)
            <li class="card"><div class="card-body py-3"><a class="fw-semibold" href="{{ route('imports.show', $import) }}">{{ $import->original_name }}</a> <span class="text-secondary">· {{ $import->status }}</span></div></li>
        @empty
            <li>@include('components.empty', ['icon' => 'bi-upload', 'title' => 'Aucun import.'])</li>
        @endforelse
    </ul>
@endsection
