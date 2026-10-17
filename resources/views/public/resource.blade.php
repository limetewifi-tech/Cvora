@extends('layouts.public')
@section('document-title', $guide['title'].' — CVora')
@section('content')
    <article class="mx-auto max-w-3xl px-4 py-14">
        <a href="{{ route('resources') }}" class="text-sm font-semibold text-brand-700">Ressources</a>
        <h1 class="mt-3 text-4xl font-semibold">{{ $guide['title'] }}</h1>
        <div class="mt-6 space-y-4 text-slate-700">
            @foreach ($guide['body'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>
@endsection
