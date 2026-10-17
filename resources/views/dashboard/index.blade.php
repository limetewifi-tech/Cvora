@extends('layouts.app')
@section('document-title', 'Vue d\'ensemble')
@section('content')
    <h1 class="text-3xl font-semibold">Bonjour {{ auth()->user()->profile?->first_name ?: auth()->user()->name }}</h1>
    <p class="mt-2 text-slate-600">Vos chiffres proviennent uniquement de votre compte.</p>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <article class="card p-5"><p class="text-sm text-slate-500">CV enregistrés</p><p class="mt-2 text-3xl font-semibold">{{ $resumeCount }}</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Lettres enregistrées</p><p class="mt-2 text-3xl font-semibold">{{ $letterCount }}</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Candidatures</p><p class="mt-2 text-3xl font-semibold">{{ $applicationCount }}</p></article>
    </div>
    <div class="mt-6 grid gap-4 lg:grid-cols-[1.4fr_.8fr]">
        <section class="card p-5">
            <h2 class="text-lg font-semibold">Derniers documents</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($recent as $item)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div><p class="text-xs uppercase tracking-wide text-slate-500">{{ $item['type'] }}</p><a class="font-medium text-brand-700" href="{{ $item['url'] }}">{{ $item['title'] }}</a></div>
                        <time class="text-xs text-slate-500">{{ $item['updated_at']?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time>
                    </li>
                @empty
                    <li class="py-3 text-sm text-slate-600">Aucun document pour le moment.</li>
                @endforelse
            </ul>
        </section>
        <aside class="space-y-4">
            <section class="card p-5">
                <h2 class="text-lg font-semibold">Offre actuelle</h2>
                <p class="mt-2 font-medium">{{ $subscription?->plan?->name ?? 'Aucune offre' }}</p>
                <p class="text-sm text-slate-600">{{ $credits }} crédits IA disponibles</p>
                <a class="btn btn-secondary mt-4" href="{{ route('billing.index') }}">Voir la facturation</a>
            </section>
            <section class="card p-5">
                <h2 class="text-lg font-semibold">Profil {{ $completion['percent'] }} %</h2>
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($completion['checks'] as $label => $done)
                        <li>{{ $done ? '✓' : '○' }} {{ $label }}</li>
                    @endforeach
                </ul>
                <a class="mt-3 inline-block text-sm font-semibold text-brand-700" href="{{ route('profile.edit') }}">Compléter le profil</a>
            </section>
        </aside>
    </div>
    <section class="mt-6">
        <h2 class="text-lg font-semibold">Actions rapides</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('cvs.create') }}">Créer un CV</a>
            <a class="btn btn-secondary" href="{{ route('imports.create') }}">Importer</a>
            <a class="btn btn-secondary" href="{{ route('letters.create') }}">Nouvelle lettre</a>
            <a class="btn btn-secondary" href="{{ route('assistant.index') }}">Assistant IA</a>
        </div>
    </section>
    @if ($notifications->isNotEmpty())
        <section class="card mt-6 p-5">
            <h2 class="text-lg font-semibold">Notifications</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($notifications as $notification)
                    <li class="flex items-center justify-between gap-3">
                        <span>{{ $notification->data['body'] ?? 'Notification' }}</span>
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="text-brand-700" type="submit">Marquer comme lue</button></form>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
