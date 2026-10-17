@extends('layouts.app')
@section('document-title', 'Lettres de motivation')
@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h1 class="h2 mb-0">Lettres de motivation</h1>
        <a class="btn btn-primary" href="{{ route('letters.create') }}">Nouvelle lettre</a>
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
