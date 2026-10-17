@extends('layouts.admin')
@section('document-title', 'Modèles')
@section('content')
    <h1 class="text-3xl font-semibold">Modèles de CV</h1>
    @foreach ($templates as $template)
        <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="card mt-4 grid gap-3 p-4 md:grid-cols-2">
            @csrf @method('PUT')
            <input class="field" name="name" value="{{ $template->name }}">
            <input class="field" name="sort_order" value="{{ $template->sort_order }}">
            <textarea class="field md:col-span-2" name="description">{{ $template->description }}</textarea>
            <label class="text-sm"><input type="checkbox" name="is_premium" value="1" @checked($template->is_premium)> Premium</label>
            <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked($template->is_active)> Actif</label>
            <button class="btn btn-secondary md:col-span-2" type="submit">Enregistrer</button>
        </form>
    @endforeach
@endsection
