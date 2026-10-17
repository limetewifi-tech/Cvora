@extends('layouts.public')
@section('document-title', $guide['title'].' — CVora')
@section('content')
    <article class="container cvora-prose py-5">
        <a href="{{ route('resources') }}" class="fw-semibold"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Ressources</a>
        <h1 class="display-5 fw-semibold mt-3">{{ $guide['title'] }}</h1>
        <div class="vstack gap-3 mt-4">
            @foreach ($guide['body'] as $paragraph)
                <p class="mb-0">{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>
@endsection
