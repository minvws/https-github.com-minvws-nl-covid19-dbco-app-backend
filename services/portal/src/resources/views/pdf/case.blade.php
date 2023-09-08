<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>{{ $name }}</title>

    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0">
    <style>
        @page {
            margin: 0 0;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            position: relative;
            padding: 2cm;
        }

        h1 {
            margin: 0 0 10px 0;
            padding: 0;
            font-size: 28px;
        }

        table {
            width: 100%;
            margin: 0 0 10px 0;
            padding: 0;
        }

        tr, td, th {
            margin: 0;
            padding: 0;
        }

        table td, table th {
            vertical-align: top;
            text-align: left;
            padding: 4px;
        }
    </style>
</head>

<body>
<h1>Dossier Export</h1>

<table>

    @include('pdf.loop', ['data' => $fragments])

</table>

<h1>Contacten</h1>

<table>

    @foreach ($contacts as $contact)

        @include('pdf.loop', ['data' => $contact])

        @if (!$loop->last)
            <tr>
                <td colspan="2">{{ str_repeat('-', 128) }}</td>
            </tr>
        @endif

    @endforeach

</table>

<h1>Contexten</h1>

<table>

    @foreach ($contexts as $context)

        @include('pdf.loop', ['data' => $context])

        @if (!$loop->last)
            <tr>
                <td colspan="2">{{ str_repeat('-', 128) }}</td>
            </tr>
        @endif

    @endforeach

</table>

<h1>Taken en Acties</h1>

@foreach ($callToActions as $callToAction)

    <h2 class="call-to-action-subject">{{ $callToAction['subject'] }}</h2>

    @if ($callToAction['deletedAt'])
        <p>Afgerond op {{ $callToAction['deletedAt'] }}</p>
    @endif

    <p>{{ $callToAction['description'] }}</p>

    <table>
        <tr>
            <td>createdAt</td>
            <td>{{ $callToAction['createdAt'] }}</td>
        </tr>
        <tr>
            <td>expiresAt</td>
            <td>{{ $callToAction['expiresAt'] }}</td>
        </tr>
        <tr>
            <td>createdBy</td>
            <td>{{ $callToAction['userRoles'] }}</td>
        </tr>
    </table>

    <h3>Timeline</h3>

    @foreach ($callToAction['events'] as $event)
        <p class="call-to-action-event">{{ $event['callToActionEvent'] }} op {{ $event['datetime'] }}</p>
        @isset($event['note'])
            <blockquote class="call-to-action-note">{{ $event['note'] }}</blockquote>
        @endisset
    @endforeach

    @if (count($callToAction['events']) === 30)
        <p>Er zijn meer gebeurtenissen of notities op deze taak. Een werkverdeler kan de case heropenen zodat een BCO-er die in het dossier kan bekijken.</p>
    @endif
@endforeach

@if (count($callToActions) === 10))
    <p>Er zijn meer taken op dit dossier. Een werkverdeler kan de case heropenen zodat een BCO-er die in het dossier kan bekijken</p>
@endif

</body>
</html>
