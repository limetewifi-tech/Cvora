@php
    $keys = $keys ?? array_column($document['sections'], 'key');
    $variant = $variant ?? 'rule';
@endphp
@if (($showSummary ?? false) && ($document['summary'] ?? '') !== '')
    <div class="section">
        <h2 class="h-{{ $variant }}">{{ $summaryLabel ?? 'Profil' }}</h2>
        <p>{!! nl2br(e($document['summary'])) !!}</p>
    </div>
@endif
@foreach ($keys as $key)
    @php $section = $document['by'][$key] ?? null; @endphp
    @continue(! $section)
    <div class="section">
        <h2 class="h-{{ $variant }}">{{ $section['label'] }}</h2>
        @if (in_array($key, ['skills', 'languages', 'interests'], true) && ($asChips ?? false))
            <p class="chips">
                @foreach ($section['items'] as $item)
                    <span class="tag">{{ $item['primary'] }}@if ($item['secondary'] !== '') · {{ $item['secondary'] }}@endif</span>
                @endforeach
            </p>
        @elseif ($key === 'skills' && ($asLevels ?? false))
            @foreach ($section['items'] as $item)
                <table class="skill-row">
                    <tr>
                        <td>{{ $item['primary'] }}</td>
                        <td class="level">{{ $item['secondary'] }}</td>
                    </tr>
                </table>
            @endforeach
        @else
            @foreach ($section['items'] as $item)
                <div class="item">
                    <p class="primary">{{ $item['primary'] }}</p>
                    @if ($item['secondary'] !== '' || $item['dates'] !== '')
                        <p class="meta">{{ trim($item['secondary'].($item['dates'] !== '' ? ' · '.$item['dates'] : '')) }}</p>
                    @endif
                    @if ($item['body'] !== '')
                        <p>{!! nl2br(e($item['body'])) !!}</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endforeach
