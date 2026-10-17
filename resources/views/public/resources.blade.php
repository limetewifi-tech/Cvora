@extends('layouts.public')
@section('document-title', 'Ressources — CVora')
@section('content')
    <div class="container cvora-wide py-5">
        <h1 class="display-5 fw-semibold">Ressources</h1>
        <div class="vstack gap-3 mt-4">
            @foreach ($guides as $guide)
                <a href="{{ route('resources.show', $guide['slug']) }}" class="card text-decoration-none text-body">
                    <div class="card-body">
                        <h2 class="h4">{{ $guide['title'] }}</h2>
                        <p class="text-secondary mb-0">{{ $guide['excerpt'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
