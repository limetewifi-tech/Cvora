@extends('layouts.admin')
@section('document-title', 'Modèles')
@section('content')
    <h1 class="h2">Modèles de CV</h1>
    @foreach ($templates as $template)
        <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="card mt-3">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ $template->name }}"></div>
                    <div class="col-md-4"><label class="form-label">Ordre</label><input class="form-control" name="sort_order" value="{{ $template->sort_order }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ $template->description }}</textarea></div>
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_premium" id="premium-{{ $template->id }}" value="1" @checked($template->is_premium)>
                            <label class="form-check-label" for="premium-{{ $template->id }}">Premium</label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="active-{{ $template->id }}" value="1" @checked($template->is_active)>
                            <label class="form-check-label" for="active-{{ $template->id }}">Actif</label>
                        </div>
                    </div>
                    <div class="col-12"><button class="btn btn-outline-primary" type="submit">Enregistrer</button></div>
                </div>
            </div>
        </form>
    @endforeach
@endsection
