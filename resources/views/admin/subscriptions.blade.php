@extends('layouts.admin')
@section('document-title', 'Abonnements')
@section('content')
    <h1 class="text-3xl font-semibold">Abonnements</h1>
    <ul class="mt-6 space-y-2">
        @foreach ($subscriptions as $subscription)
            <li class="card flex items-center justify-between gap-3 p-4 text-sm">
                <span>{{ $subscription->user?->email }} · {{ $subscription->plan?->name }} · {{ $subscription->status->label() }} · {{ $subscription->credits_remaining }} crédits</span>
                @if ($subscription->status->value === 'active')
                    <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}">@csrf<button class="btn btn-secondary" type="submit">Annuler</button></form>
                @endif
            </li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
