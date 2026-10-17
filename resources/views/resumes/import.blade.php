@extends('layouts.app')
@section('document-title', 'Importer un CV')
@section('content')
    <h1 class="text-3xl font-semibold">Importer un CV</h1>
    <p class="mt-2 max-w-2xl text-slate-600">PDF texte ou Word .docx, {{ $maxKb }} Ko maximum. Le fichier est analysé, jamais exécuté. Vous confirmez les champs avant l'enregistrement.</p>
    @if (! $enabled)
        <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm">{{ $disabledMessage }}</p>
    @else
        <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="card mt-6 max-w-xl space-y-4 p-6">
            @csrf
            <div><label class="label" for="file">Fichier</label><input class="field" id="file" type="file" name="file" accept=".pdf,.docx,application/pdf" required></div>
            <button class="btn btn-primary" type="submit">Analyser le fichier</button>
        </form>
    @endif
    <h2 class="mt-8 text-lg font-semibold">Imports récents</h2>
    <ul class="mt-3 space-y-2 text-sm">
        @forelse ($imports as $import)
            <li><a class="text-brand-700" href="{{ route('imports.show', $import) }}">{{ $import->original_name }}</a> · {{ $import->status }}</li>
        @empty
            <li class="text-slate-600">Aucun import.</li>
        @endforelse
    </ul>
@endsection
