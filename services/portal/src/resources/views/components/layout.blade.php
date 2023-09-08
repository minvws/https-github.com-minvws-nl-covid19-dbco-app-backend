<!DOCTYPE html>
<html lang="nl" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - GGD BCO Portaal</title>
    <base href="{{ config('app.url') }}" />

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5616ff">
    <meta name="msapplication-TileColor" content="#5616ff">
    <meta name="theme-color" content="#5616ff">

    @vite(['resources/js/app.js', 'resources/scss/app.scss'])

    <script nonce="{{ csp_nonce() }}">
        window.config = {!! $frontendConfiguration->toJson() !!};
    </script>
</head>
<body>
    {{ $slot }}
</body>
</html>
