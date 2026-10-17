@extends('layouts.public')
@section('document-title', 'Tarifs — CVora')
@section('content')
    <div class="mx-auto max-w-6xl px-4 py-14">
        <h1 class="text-4xl font-semibold">Tarifs</h1>
        <p class="mt-3 max-w-2xl text-slate-600">Trois niveaux configurables. Le paiement réel n'est activé qu'après une confirmation vérifiée du prestataire.</p>
        @include('public.partials.plans', ['plans' => $plans])
    </div>
@endsection
