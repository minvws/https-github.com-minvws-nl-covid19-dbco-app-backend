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
        'returnUrl' => '/case/' . $case->uuid,
        'returnTitle' => 'Terug naar case'
    ])
    <!-- End of navbar component -->
        <div class="row">
            <div class="col ml-5">

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
        <table class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-hover table-ggd">
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
                            <input type="text" size="10" id="remote_{{ $task['task.uuid'] }}" value="{{ $task['task.exportId'] }}"/>
                            <input type="checkbox" class="chk-upload-completed" id="upload_{{ $task['task.uuid'] }}" />
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
