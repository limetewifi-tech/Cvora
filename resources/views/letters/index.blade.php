@extends('layouts.app')
@section('document-title', 'Lettres de motivation')
@section('content')
    <div class="flex items-center justify-between gap-3"><h1 class="text-3xl font-semibold">Lettres de motivation</h1><a class="btn btn-primary" href="{{ route('letters.create') }}">Nouvelle lettre</a></div>
    <div class="mt-6 space-y-3">
        @forelse ($letters as $letter)
            <article class="card flex items-center justify-between gap-3 p-4"><div><h2 class="font-semibold">{{ $letter->title }}</h2><p class="text-sm text-slate-500">{{ $letter->position }} {{ $letter->company }}</p></div><a class="btn btn-secondary" href="{{ route('letters.edit', $letter) }}">Modifier</a></article>
        @empty
            <p class="text-slate-600">Aucune lettre enregistrée.</p>
        @endforelse
    </div>
@endsection
