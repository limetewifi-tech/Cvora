@extends('layouts.app')
@section('document-title', 'Mes candidatures')
@section('content')
    <h1 class="text-3xl font-semibold">Mes candidatures</h1>
    <form method="POST" action="{{ route('applications.store') }}" class="card mt-6 grid gap-3 p-5 sm:grid-cols-2">
        @csrf
        <input class="field" name="company" placeholder="Organisation" required>
        <input class="field" name="position" placeholder="Poste" required>
        <select class="field" name="status">@foreach ($statuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select>
        <input class="field" type="date" name="applied_at">
        <select class="field" name="resume_id"><option value="">CV</option>@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select>
        <select class="field" name="cover_letter_id"><option value="">Lettre</option>@foreach ($letters as $letter)<option value="{{ $letter->id }}">{{ $letter->title }}</option>@endforeach</select>
        <select class="field sm:col-span-2" name="job_offer_id"><option value="">Offre liée</option>@foreach ($offers as $offer)<option value="{{ $offer->id }}">{{ $offer->title }}</option>@endforeach</select>
        <textarea class="field min-h-20 sm:col-span-2" name="notes" placeholder="Notes"></textarea>
        <button class="btn btn-primary sm:col-span-2" type="submit">Ajouter la candidature</button>
    </form>
    <div class="mt-6 space-y-3">
        @forelse ($applications as $application)
            <form method="POST" action="{{ route('applications.update', $application) }}" class="card grid gap-2 p-4 sm:grid-cols-4">
                @csrf @method('PUT')
                <input class="field" name="company" value="{{ $application->company }}">
                <input class="field" name="position" value="{{ $application->position }}">
                <select class="field" name="status">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>@endforeach</select>
                <input type="hidden" name="notes" value="{{ $application->notes }}">
                <button class="btn btn-secondary" type="submit">Mettre à jour</button>
            </form>
        @empty
            <p class="text-slate-600">Aucune candidature suivie.</p>
        @endforelse
    </div>
@endsection
