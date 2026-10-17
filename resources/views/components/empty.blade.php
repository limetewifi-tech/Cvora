@props(['icon' => 'bi-inbox', 'title', 'action' => null, 'href' => null])
<div {{ $attributes->merge(['class' => 'cvora-empty card']) }}>
    <div class="card-body">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <p class="mt-3 mb-3 text-secondary">{{ $title }}</p>
        @if ($action && $href)
            <a class="btn btn-primary" href="{{ $href }}">{{ $action }}</a>
        @endif
    </div>
</div>
