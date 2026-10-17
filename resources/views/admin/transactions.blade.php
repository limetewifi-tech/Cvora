@extends('layouts.admin')
@section('document-title', 'Transactions')
@section('content')
    <h1 class="h2">Transactions</h1>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($transactions as $transaction)
            <li class="card"><div class="card-body py-3">{{ $transaction->reference }} · {{ $transaction->user?->email }} · {{ $transaction->plan?->name }} · {{ $transaction->amount }} {{ $transaction->currency }} · {{ $transaction->status->label() }} · {{ $transaction->provider }}</div></li>
        @empty
            <li class="text-secondary">Aucune transaction.</li>
        @endforelse
    </ul>
    <div class="mt-3">{{ $transactions->links() }}</div>
@endsection
