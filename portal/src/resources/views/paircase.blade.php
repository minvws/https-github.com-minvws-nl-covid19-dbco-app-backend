<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GGD BCO portaal - Case detail</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<?php $questionNr = 1; ?>

<div class="container-xl questionform">

    @include ('navbar')
    <!-- End of navbar component -->
        <div class="row">
            <div class="col ml-5 mr-5">

    <!-- Start of table title component -->
    <div class="align-items-end  mb-3 mt-5">
        <h3 class="mb-0"><div class="question-nr">6</div> Deel de code met de index</h3>
        <p class="mt-2 mb-0  ml-auto">Met deze code heeft de index toegang tot de contacten uit de aanleverlijst.</p>
    </div>
    <!-- End of table title component -->
    <div class="mt-4 mb-4 bg-white w-25 p-4 text-center">
        <h2>{{ $pairingCode }}</h2>
    </div>

    <div class="btn-group">
        <a href="/" class="btn btn-primary">Terug naar case overzicht</a>
    </div>
        </div>
</div>
</div>
<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
