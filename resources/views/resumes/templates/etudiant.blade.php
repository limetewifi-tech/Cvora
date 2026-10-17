<div class="band">
    <h1 style="font-size:20pt;">{{ $document['name'] }}</h1>
    <p>{{ $document['headline'] }}</p>
    <p style="font-size:10pt;margin-top:6px;">{{ implode(' · ', $document['contact']) }}</p>
</div>
<div class="pad">
    @php $education = collect($document['sections'])->firstWhere('key', 'education'); @endphp
    @if ($education)
        <div class="section edu">
            <h2>Formation et diplômes</h2>
            @foreach ($education['items'] as $item)
                <div class="item"><p class="primary">{{ $item['primary'] }}</p><p class="meta">{{ trim($item['secondary'].' '.$item['dates']) }}</p><p>{!! nl2br(e($item['body'])) !!}</p></div>
            @endforeach
        </div>
    @endif
    @include('resumes.templates._flow', ['skip' => ['education']])
</div>
