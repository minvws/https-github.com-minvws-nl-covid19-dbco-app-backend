@foreach ($data as $fragmentKey => $fragment)

    @foreach($fragment as $key => $value)

        <tr>
            <td>{{ $key }}</td>
            <td>@include('pdf.value', compact('value'))</td>
        </tr>

    @endforeach

@endforeach
