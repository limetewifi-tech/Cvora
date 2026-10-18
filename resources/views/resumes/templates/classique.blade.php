<div class="sheet-classic">
    <table class="identity">
        <tr>
            <td>
                <h1>{{ $document['name'] }}</h1>
                @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
                @if ($document['contact'] !== [])<p class="meta">{{ implode(' · ', $document['contact']) }}</p>@endif
            </td>
            <td class="photo-cell">@include('resumes.templates._photo')</td>
        </tr>
    </table>
    <div class="double-rule"></div>
    @include('resumes.templates._blocks', ['keys' => array_column($document['sections'], 'key'), 'variant' => 'center', 'showSummary' => true])
</div>
