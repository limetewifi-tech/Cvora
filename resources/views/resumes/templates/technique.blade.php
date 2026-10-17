<div class="mast">
    <h1>{{ $document['name'] }}</h1>
    <p>{{ $document['headline'] }}</p>
    <p style="margin-top:6px;font-size:9pt;">{{ implode(' | ', $document['contact']) }}</p>
</div>
<div class="pad">
    @php $skills = collect($document['sections'])->firstWhere('key', 'skills'); @endphp
    @if ($skills)
        <div class="section"><h2 style="color:#2563EB;">Compétences</h2><p>@foreach ($skills['items'] as $item)<span class="tag">{{ $item['primary'] }}</span>@endforeach</p></div>
    @endif
    @include('resumes.templates._flow', ['skip' => ['skills'], 'asTags' => false])
</div>
