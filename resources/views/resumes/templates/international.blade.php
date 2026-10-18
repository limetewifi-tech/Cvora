<div class="sheet-elegant">
    <table class="band">
        <tr>
            <td class="photo-cell">@include('resumes.templates._photo')</td>
            <td>
                <h1>{{ $document['name'] }}</h1>
                @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
                @if ($document['contact'] !== [])<p class="meta">{{ implode(' · ', $document['contact']) }}</p>@endif
            </td>
        </tr>
    </table>
    <table class="split">
        <tr>
            <td class="narrow">
                @include('resumes.templates._blocks', ['keys' => ['skills', 'languages', 'interests', 'certifications'], 'variant' => 'bar'])
            </td>
            <td>
                @include('resumes.templates._blocks', [
                    'keys' => ['experiences', 'education', 'projects', 'references'],
                    'variant' => 'bar',
                    'showSummary' => true,
                ])
            </td>
        </tr>
    </table>
</div>
