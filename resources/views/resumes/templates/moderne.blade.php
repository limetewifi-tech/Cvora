<div class="mast">
    <h1 style="font-size:22pt;">{{ $document['name'] }}</h1>
    @if ($document['headline'])<p>{{ $document['headline'] }}</p>@endif
    @if ($document['contact'])<p style="margin-top:8px;font-size:10pt;">{{ implode(' · ', $document['contact']) }}</p>@endif
</div>
<div class="pad">@include('resumes.templates._flow')</div>
