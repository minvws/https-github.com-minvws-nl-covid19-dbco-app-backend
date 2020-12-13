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

<!-- clipboard buffer -->
<input id="clipboard" class="clipboard-offscreen" aria-hidden="true">

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
                        <div class="row copyable" data-copyvalue="{{ $case->name }}">
                            <div class="col col-4">Naam (volledig)</div>
                            <div class="col">
                                {{ $case->name }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>

                            </div>
                        </div>
                        <div class="row copyable" data-copyvalue="{{ $case->caseId }}">
                            <div class="col col-4">HPZone casenummer</div>
                            <div class="col">
                                {{ $case->caseId }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>
                            </div>
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
                        <div class="row copyable" data-copyvalue="{{ $user->name }}">
                            <div class="col col-4">Naam (volledig)</div>
                            <div class="col">
                                {{ $user->name }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>

                            </div>
                        </div>
                        <div class="row copyable" data-copyvalue="-">
                            <div class="col col-4">Datum contactonderzoek</div>
                            <div class="col">
                                -
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>
                            </div>
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
                        <div class="row copyable" data-copyvalue="{{ $case->dateOfSymptomOnset }}">
                            <div class="col col-4">Datum eerste ziektedag (EZD)</div>
                            <div class="col">
                                {{ $case->dateOfSymptomOnset }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row copyable" data-copyvalue="-">
                            <div class="col col-4">Datum start besmettelijke periode</div>
                            <div class="col">
                                -
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end of section -->
            <?php
            $groupTitles = [
                '1' => ['title' => '1 - Huisgenoten', 'postfix' => 'van de huisgenoot'],
                '2a' => ['title' => '2a - Nauwe contacten', 'postfix' => 'van het nauwe contact'],
                '2b' => ['title' => '2b - Nauwe contacten', 'postfix' => 'van het nauwe contact'],
                '3' => ['title' =>'3 - Overige contacten', 'postfix' => 'van het overig contact']
            ];

            $fieldLabels = [
                'lastname' => ['label' => 'Achternaam', 'postfix' => true],
                'firstname' => ['label' => 'Voornaam'],
                'email' => ['label' => 'E-mailadres'],
                'phonenumber' => ['label' => 'Telefoonnummer'],
                'label' => ['label' => 'Naam', 'postfix' => true],
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
                                    <h6 class="mb-2">{{ $groupTitles[$category]['title'] }}</h6>
                                </div>
                            </div>
                            @foreach ($tasks as $task)
                                <div class="case-task">
                                    @foreach ($task['data'] as $key => $value)
                                        <div class="row copyable" data-copyvalue="{{ $value->copyValue ?? '-' }}">
                                            <div class="col-4">
                                                {{ $fieldLabels[$key]['label'] ?? $key }}
                                                @if ($fieldLabels[$key]['postfix'] ?? false)
                                                    {{ $groupTitles[$category]['postfix'] ?? '' }}
                                                @endif
                                            </div>
                                            <div class="col">
                                                {{ $value->displayValue ?? '-'}}
                                                <div class="float-right">
                                                    @if ($value->isUpdated ?? false)
                                                        <button class="btn btn-outline-secondary btn-sm py-0 new-data">Nieuwe gegevens</button>
                                                    @endif
                                                    <span class="row-action copy">Kopieer</span>
                                                    <span class="row-status"></span>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="row copyable" data-copyvalue="{{ $task['dateoflastexposure']->copyValue ?? '-' }}">
                                        <div class="col-4">
                                            Laatste contactmoment
                                        </div>
                                        <div class="col">
                                            {{ $task['dateoflastexposure']->displayValue ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <!-- spacer -->
                                    </div>
                                    <div class="invisible">
                                        @if ($task['needsExport'])
                                            <input type="text" size="10" id="remote_{{ $task['uuid']->value }}"
                                                       value="{{ $task['exportId']->value }}"/>
                                            <input type="checkbox" class="chk-upload-completed"
                                                       id="upload_{{ $task['uuid']->value }}"/>
                                        @else
                                            {{ $task['exportId']->value }}
                                        @endif
                                    </div>
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
