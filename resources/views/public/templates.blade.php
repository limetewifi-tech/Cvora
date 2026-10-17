@extends('layouts.public')
@section('document-title', 'Modèles de CV — CVora')
@section('content')
    <div class="mx-auto max-w-6xl px-4 py-14">
        <h1 class="text-4xl font-semibold">Modèles de CV</h1>
        <p class="mt-3 max-w-2xl text-slate-600">Chaque modèle a sa structure. L'aperçu utilise des données fictives, clairement identifiées.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($templates as $template)
                <a href="{{ route('templates.example', $template) }}" class="card overflow-hidden">
                    <iframe title="Aperçu {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="pointer-events-none h-56 w-full" loading="lazy"></iframe>
                    <div class="p-4">
                        <h2 class="font-semibold">{{ $template->name }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $template->description }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
