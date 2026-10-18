<div class="sheet-technical">
    <table class="identity tech-head">
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
                @include('resumes.templates._blocks', [
                    'keys' => ['skills', 'projects', 'certifications', 'languages', 'interests'],
                    'variant' => 'tech',
                    'asLevels' => true,
                ])
            </td>
            <td>
                @include('resumes.templates._blocks', [
                    'keys' => ['experiences', 'education', 'references'],
                    'variant' => 'rule',
                    'showSummary' => true,
                ])
            </td>
        </tr>
    </table>
</div>
