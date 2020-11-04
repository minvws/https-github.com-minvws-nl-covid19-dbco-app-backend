<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GGD BCO portaal - Case export</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body>

<!-- todo make something here that is horitontally scrollable -->
<div class="container-xl">

    <!-- Start of navbar component -->
    <nav class="navbar  navbar-expand-lg  navbar-light  bg-transparent  pl-0  pr-0  w-100">
        <a href="/case/{{ $case->uuid }}" class="btn  btn-light  rounded-pill">
            <i class="icon  icon--arrow-left  icon--m0"></i> Terug naar case
        </a>

        <button class="navbar-toggler  ml-auto  bg-white"
                type="button"
                data-toggle="collapse"
                data-target="#navbarToggler"
                aria-controls="navbarToggler"
                aria-expanded="false" aria-label="Navigatie tonen">
            <span class="navbar-toggler-icon"></span>
        </button>

        @include ('identitybar')
    </nav>
    <!-- End of navbar component -->

    <!-- Start of page title component -->
    <h2 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
        <span class="font-weight-bold">{{ $case->name }}</span>
    </h2>
    <!-- End of page title component -->
    <p>
        Casenr: {{ $case->caseId }}
        <br/>Eerste ziektedag: {{ $case->dateOfSymptomOnset != null ? $case->dateOfSymptomOnset->format('l j F') : ''}}
    </p>

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
        <table class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-hover  table-form  table-ggd">
            <!--
                Modify the col definitions in the colgroup below to change the widths of the the columns.
                The w-* classes will be automatically generated based on the $sizes array which is defined in the scss/_variables.scss
            -->
            <colgroup>
                @foreach ($headers as $fieldUuid => $header)
                    <col class="w-auto" />
                @endforeach
                <col class="w-auto" />
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
                            <input style="width: auto; display: inline; position: relative;" type="text" size="10" id="remote_{{ $task['task.uuid'] }}" value="{{ $task['task.exportId'] }}"/>
                            <input style="width: auto; display: inline; position: relative;" type="checkbox" class="chk-upload-completed" id="upload_{{ $task['task.uuid'] }}" />
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

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
