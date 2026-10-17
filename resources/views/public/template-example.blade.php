@extends('layouts.public')
@section('document-title', $template->name.' — modèle CVora')
@section('content')
    <div class="mx-auto max-w-4xl px-4 py-10">
        <p class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Cet aperçu utilise des données fictives pour illustrer la mise en page « {{ $template->name }} ». Ce n'est pas un témoignage.</p>
        <h1 class="mt-6 text-3xl font-semibold">{{ $template->name }}</h1>
        <p class="mt-2 text-slate-600">{{ $template->description }}</p>
        <iframe title="Exemple {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="mt-6 h-[900px] w-full rounded-2xl border border-slate-200 bg-white"></iframe>
        <a class="btn btn-primary mt-6" href="{{ route('register') }}">Utiliser ce modèle</a>
    </div>
@endsection
