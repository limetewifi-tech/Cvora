<div style="border-left:6px solid #1F2937;padding-left:12px;">
    <h1 style="font-size:18pt;">{{ $document['name'] }}</h1>
    <p>{{ $document['headline'] }}</p>
</div>
<table>
    <tr><td class="label">Courriel</td><td>{{ $document['contact'][0] ?? '' }}</td></tr>
    <tr><td class="label">Téléphone</td><td>{{ $document['contact'][1] ?? '' }}</td></tr>
    <tr><td class="label">Adresse</td><td>{{ $document['contact'][2] ?? '' }}</td></tr>
</table>
<div class="pad">@include('resumes.templates._flow')</div>
