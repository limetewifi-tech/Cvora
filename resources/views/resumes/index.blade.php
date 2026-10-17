@extends('layouts.app')
@section('document-title', 'Mes CV')
@section('content')
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-3xl font-semibold">Mes CV</h1>
        <a class="btn btn-primary" href="{{ route('cvs.create') }}">Créer un CV</a>
    </div>
    <div class="mt-6 grid gap-4">
        @forelse ($resumes as $resume)
            <article class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">{{ $resume->title }}</h2>
                    <p class="text-sm text-slate-500">{{ $resume->template?->name }} · modifié {{ $resume->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="btn btn-secondary" href="{{ route('cvs.edit', $resume) }}">Modifier</a>
                    <a class="btn btn-secondary" href="{{ route('cvs.preview', $resume) }}" target="_blank" rel="noopener">Aperçu</a>
                    <form method="POST" action="{{ route('cvs.duplicate', $resume) }}">@csrf<button class="btn btn-ghost" type="submit">Dupliquer</button></form>
                </div>
            </article>
        @empty
            <p class="text-slate-600">Vous n'avez pas encore de CV.</p>
        @endforelse
    </div>
@endsection
