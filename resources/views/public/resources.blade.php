@extends('layouts.public')
@section('document-title', 'Ressources — CVora')
@section('content')
    <div class="mx-auto max-w-4xl px-4 py-14">
        <h1 class="text-4xl font-semibold">Ressources</h1>
        <div class="mt-8 space-y-4">
            @foreach ($guides as $guide)
                <a href="{{ route('resources.show', $guide['slug']) }}" class="card block p-5">
                    <h2 class="text-xl font-semibold">{{ $guide['title'] }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $guide['excerpt'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
@endsection
