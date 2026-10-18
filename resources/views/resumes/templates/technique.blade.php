<div class="mast">
    <h1>{{ $document['name'] }}</h1>
    @if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
    @if ($document['contact'])<p style="margin-top:6px;">{{ implode(' · ', $document['contact']) }}</p>@endif
</div>
<table class="layout-cols cols">
    <tr>
        <td>@include('resumes.templates._flow', ['showSummary' => false, 'skip' => ['skills', 'languages', 'certifications', 'interests', 'references']])</td>
        <td>@include('resumes.templates._flow', ['skip' => ['experiences', 'education', 'projects'], 'asTags' => true])</td>
    </tr>
</table>
