@extends('layouts.app')
@section('document-title', 'Vue d\'ensemble')
@section('content')
    <h1 class="h2">Bonjour {{ auth()->user()->profile?->first_name ?: auth()->user()->name }}</h1>
    <p class="text-secondary">Vos chiffres proviennent uniquement de votre compte.</p>
    <div class="row g-3 mt-1">
        <div class="col-sm-4"><article class="card h-100"><div class="card-body"><p class="small text-secondary mb-1">CV enregistrés</p><p class="display-6 fw-semibold mb-0">{{ $resumeCount }}</p></div></article></div>
        <div class="col-sm-4"><article class="card h-100"><div class="card-body"><p class="small text-secondary mb-1">Lettres enregistrées</p><p class="display-6 fw-semibold mb-0">{{ $letterCount }}</p></div></article></div>
        <div class="col-sm-4"><article class="card h-100"><div class="card-body"><p class="small text-secondary mb-1">Candidatures</p><p class="display-6 fw-semibold mb-0">{{ $applicationCount }}</p></div></article></div>
    </div>
    <div class="row g-3 mt-1">
        <section class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Derniers documents</h2>
                    <ul class="list-unstyled mb-0 mt-3">
                        @forelse ($recent as $item)
                            <li class="d-flex justify-content-between gap-3 py-3 border-bottom">
                                <div>
                                    <p class="small text-uppercase text-secondary mb-1">{{ $item['type'] }}</p>
                                    <a class="fw-semibold" href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                                </div>
                                <time class="small text-secondary">{{ $item['updated_at']?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time>
                            </li>
                        @empty
                            <li>@include('components.empty', ['icon' => 'bi-file-earmark', 'title' => 'Aucun document pour le moment.', 'action' => 'Créer un CV', 'href' => route('cvs.create')])</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
        <aside class="col-lg-4 vstack gap-3">
            <section class="card">
                <div class="card-body">
                    <h2 class="h5">Abonnement</h2>
                    <p class="fw-semibold mb-1">{{ $subscription?->plan?->name ?? 'Aucun abonnement' }}</p>
                    <p class="small text-secondary">{{ $credits }} crédits IA disponibles</p>
                    <a class="btn btn-outline-primary" href="{{ route('billing.index') }}">Voir la facturation</a>
                </div>
            </section>
            <section class="card">
                <div class="card-body">
                    <h2 class="h5">Profil {{ $completion['percent'] }} %</h2>
                    <div class="progress mb-3" role="progressbar" aria-valuenow="{{ $completion['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: {{ $completion['percent'] }}%"></div>
                    </div>
                    <ul class="list-unstyled small mb-3">
                        @foreach ($completion['checks'] as $label => $done)
                            <li class="mb-1"><i class="bi {{ $done ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} me-1" aria-hidden="true"></i>{{ $label }}</li>
                        @endforeach
                    </ul>
                    <a class="fw-semibold" href="{{ route('profile.edit') }}">Compléter le profil</a>
                </div>
            </section>
        </aside>
    </div>
    <section class="mt-4">
        <h2 class="h5">Actions rapides</h2>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-primary" href="{{ route('cvs.create') }}">Créer un CV</a>
            <a class="btn btn-outline-primary" href="{{ route('imports.create') }}">Importer</a>
            <a class="btn btn-outline-primary" href="{{ route('letters.create') }}">Nouvelle lettre</a>
            <a class="btn btn-outline-primary" href="{{ route('assistant.index') }}">Assistant IA</a>
            <a class="btn btn-outline-primary" href="{{ route('studio.index') }}">Studio CV</a>
        </div>
    </section>
    @if ($notifications->isNotEmpty())
        <section class="card mt-4">
            <div class="card-body">
                <h2 class="h5">Notifications</h2>
                <ul class="list-unstyled mb-0">
                    @foreach ($notifications as $notification)
                        <li class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                            <span>{{ $notification->data['body'] ?? 'Notification' }}</span>
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">Marquer comme lue</button></form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
