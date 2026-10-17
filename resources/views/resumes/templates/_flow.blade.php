@if (($document['summary'] ?? '') !== '' && ($showSummary ?? true))
    <div class="section">
        <h2>{{ $summaryLabel ?? 'Profil' }}</h2>
        <p>{!! nl2br(e($document['summary'])) !!}</p>
    </div>
@endif
@foreach ($document['sections'] as $section)
    @continue(isset($skip) && in_array($section['key'], $skip, true))
    <div class="section">
        <h2>{{ !empty($bilingual) ? $section['label'].' / '.$section['label_en'] : $section['label'] }}</h2>
        @if (($section['key'] === 'skills' || $section['key'] === 'languages') && !empty($asTags))
            <p>
                @foreach ($section['items'] as $item)
                    <span class="tag">{{ $item['primary'] }}@if ($item['secondary']) · {{ $item['secondary'] }}@endif</span>
                @endforeach
            </p>
        @else
            @foreach ($section['items'] as $item)
                <div class="item">
                    <p class="primary">{{ $item['primary'] }}</p>
                    @if ($item['secondary'] || $item['dates'])
                        <p class="meta">{{ trim($item['secondary'].($item['dates'] ? ' · '.$item['dates'] : '')) }}</p>
                    @endif
                    @if ($item['body'])
                        <p>{!! nl2br(e($item['body'])) !!}</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endforeach
