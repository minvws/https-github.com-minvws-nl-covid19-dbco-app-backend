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

<h1>Contactdossier Export</h1>

<table>

    @include('pdf.loop', ['data' => $fragments])

</table>

</body>
</html>
