@extends('layouts.public')
@section('document-title', 'Modèles de CV — CVora')
@section('content')
    <div class="container py-5">
        <h1 class="display-5 fw-semibold">Modèles de CV</h1>
        <p class="cvora-narrow text-secondary mt-3">Chaque modèle a sa structure. L'aperçu utilise des données fictives, clairement identifiées.</p>
        <div class="row g-3 mt-2">
            @foreach ($templates as $template)
                <div class="col-sm-6 col-lg-3">
                    <a href="{{ route('templates.example', $template) }}" class="card h-100 text-decoration-none text-body overflow-hidden">
                        <iframe title="Aperçu {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="cvora-frame-md w-100 border-0" loading="lazy"></iframe>
                        <div class="card-body">
                            <h2 class="h6">{{ $template->name }}</h2>
                            <p class="small text-secondary mb-0">{{ $template->description }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
