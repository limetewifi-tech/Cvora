<h1 style="font-size:20pt;">{{ $document['name'] }}</h1>
@if ($document['headline'])<p style="margin-top:4px;">{{ $document['headline'] }}</p>@endif
@if ($document['contact'])<p class="meta" style="margin-top:6px;">{{ implode('  ·  ', $document['contact']) }}</p>@endif
<div style="margin-top:14px;">@include('resumes.templates._flow')</div>
