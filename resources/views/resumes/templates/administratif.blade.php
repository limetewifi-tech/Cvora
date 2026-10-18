<div class="sheet-executive">
    <table class="exec-head" style="background: {{ $document['primary'] }};">
        <tr>
            <td class="photo-cell">@include('resumes.templates._photo')</td>
            <td>
                <p class="kicker">Curriculum vitae</p>
                <h1>{{ $document['name'] }}</h1>
                @if ($document['headline'] !== '')<p class="role">{{ $document['headline'] }}</p>@endif
                @if ($document['contact'] !== [])<p class="meta">{{ implode(' · ', $document['contact']) }}</p>@endif
            </td>
        </tr>
    </table>
    <div class="gold-line"></div>
    <table class="split">
        <tr>
            <td class="narrow">
                @include('resumes.templates._blocks', ['keys' => ['skills', 'languages', 'interests'], 'variant' => 'exec', 'showSummary' => true])
            </td>
            <td>
                @include('resumes.templates._blocks', ['keys' => ['experiences', 'education', 'projects', 'certifications', 'references'], 'variant' => 'exec'])
            </td>
        </tr>
    </table>
</div>
