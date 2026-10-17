@extends('layouts.app')
@section('document-title', 'Mes candidatures')
@section('content')
    <h1 class="h2">Mes candidatures</h1>
    <form method="POST" action="{{ route('applications.store') }}" class="card mt-3 needs-validation" novalidate>
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label" for="company">Organisation</label><input class="form-control" id="company" name="company" required><div class="invalid-feedback">Indiquez l'organisation.</div></div>
                <div class="col-sm-6"><label class="form-label" for="position">Poste</label><input class="form-control" id="position" name="position" required><div class="invalid-feedback">Indiquez le poste.</div></div>
                <div class="col-sm-6"><label class="form-label" for="status">Statut</label><select class="form-select" id="status" name="status">@foreach ($statuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select></div>
                <div class="col-sm-6"><label class="form-label" for="applied_at">Date</label><input class="form-control" id="applied_at" type="date" name="applied_at"></div>
                <div class="col-sm-6"><label class="form-label" for="resume_id">CV</label><select class="form-select" id="resume_id" name="resume_id"><option value="">CV</option>@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select></div>
                <div class="col-sm-6"><label class="form-label" for="cover_letter_id">Lettre</label><select class="form-select" id="cover_letter_id" name="cover_letter_id"><option value="">Lettre</option>@foreach ($letters as $letter)<option value="{{ $letter->id }}">{{ $letter->title }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label" for="job_offer_id">Offre liée</label><select class="form-select" id="job_offer_id" name="job_offer_id"><option value="">Offre liée</option>@foreach ($offers as $offer)<option value="{{ $offer->id }}">{{ $offer->title }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label" for="notes">Notes</label><textarea class="form-control" id="notes" name="notes" rows="3"></textarea></div>
                <div class="col-12"><button class="btn btn-primary" type="submit" data-loading>Ajouter la candidature</button></div>
            </div>
        </div>
    </form>
    <div class="vstack gap-3 mt-4">
        @forelse ($applications as $application)
            <form method="POST" action="{{ route('applications.update', $application) }}" class="card">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-lg-3"><label class="form-label">Organisation</label><input class="form-control" name="company" value="{{ $application->company }}"></div>
                        <div class="col-sm-6 col-lg-3"><label class="form-label">Poste</label><input class="form-control" name="position" value="{{ $application->position }}"></div>
                        <div class="col-sm-6 col-lg-3"><label class="form-label">Statut</label><select class="form-select" name="status">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>@endforeach</select></div>
                        <input type="hidden" name="notes" value="{{ $application->notes }}">
                        <div class="col-sm-6 col-lg-3"><button class="btn btn-outline-primary w-100" type="submit">Mettre à jour</button></div>
                    </div>
                </div>
            </form>
        @empty
            @include('components.empty', ['icon' => 'bi-briefcase', 'title' => 'Aucune candidature suivie.'])
        @endforelse
    </div>
@endsection
