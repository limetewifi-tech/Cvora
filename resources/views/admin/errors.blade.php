@extends('layouts.admin')
@section('document-title', 'Journaux')
@section('content')
    <h1 class="h2">Journaux techniques</h1>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($logs as $error)
            <li class="card">
                <div class="card-body">
                    <p class="fw-semibold mb-1">{{ $error->exception }}</p>
                    <p class="mb-1">{{ $error->message }}</p>
                    <p class="small text-secondary mb-0">{{ $error->file }}:{{ $error->line }} · {{ $error->url }} · {{ $error->created_at }}</p>
                </div>
            </li>
        @empty
            <li class="text-secondary">Aucun journal technique.</li>
        @endforelse
    </ul>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
