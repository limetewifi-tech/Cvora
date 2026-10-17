@props(['variant' => 'color', 'wordmark' => true])
@php
    $mark = $variant === 'light' ? '#FFFFFF' : '#2563EB';
    $fold = $variant === 'light' ? '#DBEAFE' : '#DBEAFE';
    $person = $variant === 'light' ? '#2563EB' : '#FFFFFF';
    $text = $variant === 'light' ? '#FFFFFF' : '#1F2937';
    $accent = $variant === 'light' ? '#DBEAFE' : '#2563EB';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 48 48" class="h-10 w-10 shrink-0" aria-hidden="true">
        <path fill="{{ $mark }}" d="M10 5.5h16.5L38 17v21.5a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4v-25a4 4 0 0 1 4-4z"/>
        <path fill="{{ $fold }}" d="M26.5 5.5V15a2 2 0 0 0 2 2H38"/>
        <circle cx="20" cy="22.5" r="4.2" fill="{{ $person }}"/>
        <path fill="{{ $person }}" d="M11.8 36.2c1.3-4.6 4.6-7 8.2-7s6.9 2.4 8.2 7"/>
    </svg>
    @if ($wordmark)
        <span class="font-display text-2xl font-semibold tracking-tight" style="color: {{ $text }}">CV<span style="color: {{ $accent }}">ora</span></span>
    @endif
</span>
