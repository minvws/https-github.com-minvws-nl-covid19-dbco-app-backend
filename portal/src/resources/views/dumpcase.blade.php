<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GGD BCO portaal - Case export</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<!-- todo make something here that is horitontally scrollable -->
<div class="container-xl">

    @include ('navbar', [
        'returnPath' => route('case-view', [$case->uuid])
    ])

    <div class="row">
        <div class="col ml-5">

            <!-- Page title -->
            <h1 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                Overzetten naar HPZone
            </h1>
            <!-- End of page title -->

            <!-- Section: gegevens index -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        Gegevens index
                        <div class="float-right">
                            <button class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row">
                            <div class="col col-4">Naam (volledig)</div>
                            <div class="col">{{ $case->name }}</div>
                        </div>
                        <div class="row">
                            <div class="col col-4">HPZone casenummer</div>
                            <div class="col">{{ $case->caseId }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Informatie BCO-medewerker -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        Informatie BCO-medewerker
                        <div class="float-right">
                            <button class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row">
                            <div class="col col-4">Naam (volledig)</div>
                            <div class="col">{{ $user->name }}</div>
                        </div>
                        <div class="row">
                            <div class="col col-4">Datum contactonderzoek</div>
                            <div class="col">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end of section -->

            <!-- Section: Informatie over index zelf -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        Informatie over de index zelf
                        <div class="float-right">
                            <button class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row">
                            <div class="col col-4">Datum eerste ziektedag (EZD)</div>
                            <div class="col">{{ $case->name }}</div>
                        </div>
                        <div class="row">
                            <div class="col col-4">Datum start besmettelijke periode</div>
                            <div class="col">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end of section -->
            <?php
            $groupTitles = [
                '1' => ['title' => '1 - Huisgenoten', 'nameLabelPostfix' => 'van de huisgenoot'],
                '2a' => ['title' => '2a - Nauwe contacten', 'nameLabelPostfix' => 'van het nauwe contact'],
                '2b' => ['title' => '2b - Nauwe contacten', 'nameLabelPostfix' => 'van het nauwe contact'],
                '3' => ['title' =>'3 - Overige contacten', 'nameLabelPostfix' => 'van het overig contact']
            ];
            ?>
            <!-- Section: Contactonderzoek -->
           `<div class="card mt-4 mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        Contactonderzoek
                        <div class="float-right">
                            <button class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        @foreach ($taskcategories as $category => $tasks)
                            <div class="row">
                                <div class="col">
                                    <h6 class="mb-0">{{ $groupTitles[$category]['title'] }}</h6>
                                </div>
                            </div>
                            @foreach ($tasks as $task)
                                @foreach ($task['data'] as $key => $value)
                                <div class="row">
                                    <div class="col-4">
                                        {{ $key }}
                                    </div>
                                    <div class="col">
                                        {{ $value ?? '-'}}
                                    </div>
                                </div>
                                @endforeach
                                <div class="row">
                                    <!-- spacer -->
                                </div>
                                <div class="invisible">
                                    @if ($task['enableExport'])
                                        <input type="text" size="10" id="remote_{{ $task['uuid'] }}"
                                                   value="{{ $task['exportId'] }}"/>
                                        <input type="checkbox" class="chk-upload-completed"
                                                   id="upload_{{ $task['uuid'] }}"/>
                                    @else
                                        {{ $task['exportId'] }}
                                    @endif
                                </div>
                            @endforeach
                    <!-- End of table component -->
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
