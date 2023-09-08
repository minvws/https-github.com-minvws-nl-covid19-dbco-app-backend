@if (is_array($value))
    @foreach ($value as $item)
        @include('pdf.value', ['value' => $item])
    @endforeach
@elseif (is_object($value))
    @foreach ($value as $item)
        @if (!empty($item))
            {{ $item }}<br>
        @endif
    @endforeach
@elseif (is_bool($value))
    {{ $value ? 'Ja' : 'Nee' }}
@else
    {{ $value }}
@endif
