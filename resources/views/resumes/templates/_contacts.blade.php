@foreach ($document['lines'] as $line)
    <p class="contact-line"><span class="contact-label">{{ $line['label'] }}</span><br>{{ $line['value'] }}</p>
@endforeach
