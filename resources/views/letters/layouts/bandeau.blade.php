<div class="band">
    <h1>Lettre de motivation</h1>
</div>
<div class="band-object">{{ $sheet['object'] }}</div>
<div class="pad">
    <table class="sheet-table">
        <tr>
            <td class="half">
                @foreach ($sheet['sender'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
            </td>
            <td class="half">
                @foreach ($sheet['recipient'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
                <p class="muted" style="margin-top:8px;">{{ $sheet['place'] }}</p>
            </td>
        </tr>
    </table>
    <div class="body">{!! nl2br(e($sheet['body'])) !!}</div>
    <p class="sign">{{ $sheet['name'] }}</p>
</div>
