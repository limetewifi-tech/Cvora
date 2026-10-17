@props([
    'id',
    'title',
    'action',
    'trigger' => 'Confirmer',
    'submit' => 'Confirmer',
    'triggerClass' => 'btn btn-outline-danger',
    'method' => 'DELETE',
])
<button type="button" class="{{ $triggerClass }}" data-bs-toggle="modal" data-bs-target="#{{ $id }}">
    {{ $trigger }}
</button>
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}" class="needs-validation" novalidate>
                @csrf
                @if (! in_array(strtoupper($method), ['GET', 'POST'], true))
                    @method($method)
                @endif
                <div class="modal-header">
                    <h2 class="modal-title h5" id="{{ $id }}-title">{{ $title }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">{{ $slot }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-danger" type="submit" data-loading>{{ $submit }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
