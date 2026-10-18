<table class="exec-head" style="background: {{ $document['primary'] }};">
    <tr>
        <td class="photo-cell">@include('resumes.templates._photo')</td>
        <td>
            <h1>{{ $document['name'] }}</h1>
            @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
            @if ($document['contact'] !== [])<p class="meta">{{ implode(' · ', $document['contact']) }}</p>@endif
        </td>
    </tr>
</table>
<div class="gold-line"></div>
@include('resumes.templates._blocks', ['keys' => array_column($document['sections'], 'key'), 'variant' => 'rule', 'showSummary' => true])
