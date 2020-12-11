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
                    <ul class="list-group">
                        <li class="copy-row-value list-group-item list-group-item-action">
                            <span>Naam (volledig)</span>
                            <span>{{ $case->name }}</span>
                        </li>
                        <li class="copy-row-value list-group-item list-group-item-action">
                            <span>HPZone casenummer</span>
                            <span>{{ $case->caseId }}</span>
                        </li>
                    </ul>
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
                            <div class="col col-4">Telefoonnummer</div>
                            <div class="col">-</div>
                        </div>
                        <div class="row">
                            <div class="col col-4">E-mailadres</div>
                            <div class="col">-</div>
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
            <div class="card mt-4 mb-4">
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
            '1' => '1 - Huisgenoten',
            '2a' => '2a - Nauwe contacten',
            '2b' => '2b - Nauwe contacten',
            '3' => '3 - Overige contacten'
        ];
        ?>

        @foreach ($taskcategories as $category => $tasks)
            <!-- Start of table title component -->
                <div class="d-flex  align-items-end  mb-3 mt-4">
                    <h2 class="mb-0">{{ $groupTitles[$category] }}</h2>
                </div>
                <!-- End of table title component -->

                <!-- Start of table component -->
                <table
                    class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-hover table-ggd">
                    <!--
                        Modify the col definitions in the colgroup below to change the widths of the the columns.
                        The w-* classes will be automatically generated based on the $sizes array which is defined in the scss/_variables.scss
                    -->
                    <colgroup>
                        @foreach ($headers as $fieldUuid => $header)
                            <col class="w-auto"/>
                        @endforeach
                        <col class="w-auto"/>
                    </colgroup>
                    <thead>
                    <tr>
                        @foreach ($headers as $fieldUuid => $header)
                            <th scope="col">{{ $header }}</th>
                        @endforeach

                        <th>HPZone</th>
                    </tr>

                    </thead>
                    <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            @foreach ($headers as $fieldUuid => $header)
                                <td>
                                    {{ $task[$fieldUuid] ?? ''}}
                                </td>
                            @endforeach

                            <td>
                                @if ($task['task.enableExport'])
                                    <input type="text" size="10" id="remote_{{ $task['task.uuid'] }}"
                                           value="{{ $task['task.exportId'] }}"/>
                                    <input type="checkbox" class="chk-upload-completed"
                                           id="upload_{{ $task['task.uuid'] }}"/>
                                @else
                                    {{ $task['task.exportId'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- End of table component -->
            @endforeach
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
