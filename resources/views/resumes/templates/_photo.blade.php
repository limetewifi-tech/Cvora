@if (!empty($document['photo']))
    <img class="photo photo-{{ $document['photo_shape'] }}" src="{{ $document['photo'] }}" alt="Photo de {{ $document['name'] }}">
@else
    <img class="monogram photo-{{ $document['photo_shape'] }}" src="{{ $document['mark'] }}" alt="Initiales {{ $document['initials'] }}">
@endif
