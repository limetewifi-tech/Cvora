@extends('layouts.public')
@section('document-title', 'Tarifs — CVora')
@section('content')
    <div class="container py-5">
        <h1 class="display-5 fw-semibold">Tarifs</h1>
        <p class="cvora-narrow text-secondary mt-3">Trois niveaux configurables. Le paiement réel n'est activé qu'après une confirmation vérifiée du prestataire.</p>
        @include('public.partials.plans', ['plans' => $plans])
    </div>
@endsection
