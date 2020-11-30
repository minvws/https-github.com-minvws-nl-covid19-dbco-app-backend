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
    <form action="/savecase" method="POST">
        @csrf
        <input type="hidden" id="caseUuid" name="caseUuid" value="{{ $case->uuid }}">

        @include ('navbar')

        <div class="row">
            <div class="col ml-5 mr-5">

                <!-- Start of question title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Hoe heet de index?</h3>
                </div>
                <!-- End of question title component -->
                @error('name')
                    <div class="alert alert-danger">
                        Naam is een verplicht veld, en maximaal 255 karakters.
                    </div>
                @enderror
                <input type="text" maxlength="255" class="form-control w-25" id="name" name="name" value="{{ old('name', $case->name) }}">

                <!-- Start of question title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Heb je een case nummer als referentie?</h3>
                    <p class="mt-2 mb-0  ml-auto">Bijvoorbeeld een case id uit HPZone, zodat je later makkelijk kunt zien bij wie deze gegevens horen.</p>
                </div>
                <!-- End of question title component -->
                @error('caseId')
                    <div class="alert alert-danger">
                        Een case nummer is maximaal 255 karakters.
                    </div>
                @enderror
                <input type="text" maxlength="255" class="form-control w-25" id="caseId" name="caseId" value="{{ old('caseId', $case->caseId) }}">


                <!-- Start of question title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Wat is de eerste ziektedag van de index?</h3>
                    <p class="mt-2 mb-0  ml-auto">De besmettelijke periode is twee dagen voor de eerste ziektedag tot en met vandaag.</p>
                </div>
                <!-- End of question title component -->
                @error('dateOfSymptomOnset')
                <div class="alert alert-danger">
                    Geef altijd een eerste ziektedag op.
                </div>
                @enderror
                <div>
                    <input type="hidden" class="form-control" id="dateofsymptomonset" name="dateOfSymptomOnset" value="{{ old('dateOfSymptomOnset', $case->dateOfSymptomOnset) }}" />
                </div>

                <!-- Start of table title component -->
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Ga je nu samen met de index de contacten in kaart brengen?</h3>
                    <p class="mt-2 mb-0  ml-auto">Als je wilt dat de index zelf een begin maakt met het in kaart brengen van mensen die misschien besmet zijn, dan kan dat met de app.</p>
                </div>
                <!-- End of table title component -->
                <p>
                    <button class="btn btn-outline-primary" type="button" onClick="$('#taskTable').show();">Ja</button>
                    <button class="btn btn-outline-primary" type="button" onClick="$('#taskTable').hide();">Nee</button>
                </p>
                <!-- Start of table component -->
                <table id="taskTable" class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-form  table-ggd">
                    <!--
                        Modify the col definitions in the colgroup below to change the widths of the the columns.
                        The w-* classes will be automatically generated based on the $sizes array which is defined in the scss/_variables.scss
                    -->
                    <colgroup>
                        <col class="w-25">
                        <col class="w-25">
                        <col class="w-8">
                        <col class="w-15">
                        <col class="w-15">
                        <col class="w-5">
                    </colgroup>
                    <thead>
                    <tr>
                        <th scope="col">Naam <i class="icon  icon--eye"></i></th>
                        <th scope="col">Toelichting (optioneel) <i class="icon  icon--eye"></i></th>
                        <th scope="col">Categorie</th>
                        <th scope="col">Laatste contact</th>
                        <th scope="col">Wie informeert</th>
                        <th scope="col"></th>

                    </tr>
                    </thead>

                    <tbody>
                    <?php
                        $oldTasks = old('tasks');
                        if (is_array($oldTasks) && count($oldTasks)) {
                            $tasks = $oldTasks;
                        }
                        $row=0;
                    ?>
                        @foreach ($tasks as $taskObj)
                            <?php
                                $task = (array)$taskObj;
                                if (isset($task['dateOfLastExposure']) && is_object($task['dateOfLastExposure'])) {
                                    // template can't handle object because after validation 'old' is always an array, so we need
                                    // to be able to populate the field in both old and new cases, we use the stirng format.
                                    $task['dateOfLastExposure'] = $task['dateOfLastExposure']->format('Y-m-d');
                                }
                            ?>
                            @include ('editcase_row')
                            <?php $row++; ?>
                        @endforeach
                    </tbody>

                </table>
                <!-- End of table component -->

                <!-- Start of table title component -->
                @if ($case->status == 'draft')
                <div class="align-items-end  mb-3 mt-5">
                    <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Vertel de index welke app ze moeten downloaden</h3>
                    <p class="mt-2 mb-0  ml-auto">De index heeft een app nodig die ze kunnen downloaden in de Play of AppStore waarmee ze de gegevens op een veilige manier met de GGD kunnen delen.</p>
                </div>
                @endif
                <!-- End of table title component -->

                <div class="btn-group">
                    <input type="submit" class="btn btn-primary" value=
                        @if ($case->status == 'draft')
                            "Case openen"
                        @else
                            "Case opslaan"
                        @endif
                    />
                </div>

                <p></p>
            </div>
        </div>

    </form>

</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
