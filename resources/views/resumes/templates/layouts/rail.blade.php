<table class="split">
    <tr>
        <td class="main">
            @include('resumes.templates._blocks', [
                'keys' => ['experiences', 'education', 'projects', 'certifications', 'references'],
                'variant' => 'rule',
                'showSummary' => true,
                'summaryLabel' => 'Parcours',
            ])
        </td>
        <td class="side" style="background: {{ $document['primary'] }};">
            @include('resumes.templates._photo')
            <h1>{{ $document['name'] }}</h1>
            @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
            @include('resumes.templates._contacts')
            @include('resumes.templates._blocks', ['keys' => ['skills', 'languages', 'interests'], 'variant' => 'side'])
        </td>
    </tr>
</table>
