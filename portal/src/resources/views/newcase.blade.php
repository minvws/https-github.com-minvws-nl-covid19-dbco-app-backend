<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Case detail</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body>

<div class="container-xl questionform">
    <form action="/savecase" method="POST">
        @csrf
        <input type="hidden" name="caseId" value="{{ $case->uuid }}">

        <!-- Start of navbar component -->
        <nav class="navbar  navbar-expand-lg  navbar-light  bg-transparent  pl-0  pr-0  w-100">
            <a href="/" class="btn  btn-light  rounded-pill">
                <i class="icon  icon--arrow-left  icon--m0"></i> Terug naar Cases
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

        <!-- Start of question title component -->
        <div class="align-items-end  mb-3 mt-3">
            <h3 class="mb-0"><div class="question-nr">1</div> Hoe heet de index?</h3>
        </div>
        <!-- End of question title component -->
        <input type="text" class="form-control" id="name" name="name" value="">

        <!-- Start of question title component -->
        <div class="align-items-end  mb-3 mt-3">
            <h3 class="mb-0"><div class="question-nr">2</div> Wat is de eerste ziektedag van de index?</h3>
            <p class="mt-2 mb-0  ml-auto">De besmettelijke periode is twee dagen voor de eerste ziektedag tot en met vandaag</p>
        </div>
        <!-- End of question title component -->
        <!-- TODO DATE PICKER CONFORM DESIGN -->
        <input type="text" class="form-control" id="dateofsymptomonset" name="dateofsymptomonset" value="">

        <!-- Start of table title component -->
        <div class="align-items-end  mb-3 mt-3">
            <h3 class="mb-0"><div class="question-nr">3</div> Ga je nu samen met de index de contacten in kaart brengen?</h3>
            <p class="mt-2 mb-0  ml-auto">Als je wilt dat de index zelf een begin maakt met het in kaart brengen van mensen die misschien besmet zijn, dan kan dat met de app.</p>
        </div>
        <!-- End of table title component -->

        Ja|Nee

        <!-- Start of table component -->
        <table class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-hover  table-form  table-ggd">
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
                <th scope="col">Context (optioneel) <i class="icon  icon--eye"></i></th>
                <th scope="col">Categorie</th>
                <th scope="col">Laatste contact</th>
                <th scope="col">Wie informeert</th>
                <th scope="col"></th>

            </tr>
            </thead>

            <tbody>
            <tr>
                <td>
                    <label class="sr-only" for="label">Label</label>
                    <input type="text" class="form-control" id="label" name="label[]" value="" placeholder="Voeg contact toe">
                </td>
                <td>
                    <label class="sr-only" for="context1">Context</label>
                    <input type="text" class="form-control" id="context1" name="context[]" value="" placeholder="Bijv. collega of trainer">
                </td>
                <td>
                    <label class='sr-only' for="categorie1">Categorie</label>
                    <select class="form-control" id="category1" name="category[]">
                        <option disabled selected>Selecteer</option>
                        <option>1</option>
                        <option>2b</option>
                        <option>2c</option>
                        <option>3</option>
                    </select>
                </td>
                <td>
                    <label class="sr-only" for="date1">Laatste contact</label>
                    <select class="form-control" id="lastcontact1" name="dateOfLastExposure[]">
                        <option disabled selected>Selecteer</option>
                        @for ($i = 0; $i < 14; $i++)
                            <?php
                            $date = Date::parse("-$i days")->format("Y-m-d");
                            $label = Date::parse("-$i days")->format('l j M');
                            ?>
                            <option value="{{ $date }}">{{ $label }}</option>
                        @endfor
                    </select>
                </td>
                <td>
                    <label class='sr-only' for="informeren1">Wie informeert</label>
                    <select class="form-control" id="informeren1" name="communication[]">
                        <option disabled selected>Selecteer</option>
                        <option>GGD</option>
                        <option>Index</option>
                    </select>
                </td>
                <td class="text-center">
                    <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="sr-only" for="label">Label</label>
                    <input type="text" class="form-control" id="label" name="label[]" value="" placeholder="Voeg contact toe">
                </td>
                <td>
                    <label class="sr-only" for="context1">Context</label>
                    <input type="text" class="form-control" id="context1" name="context[]" value="" placeholder="Bijv. collega of trainer">
                </td>
                <td>
                    <label class='sr-only' for="categorie1">Categorie</label>
                    <select class="form-control" id="category1" name="category[]">
                        <option disabled selected>Selecteer</option>
                        <option>1</option>
                        <option>2b</option>
                        <option>2c</option>
                        <option>3</option>
                    </select>
                </td>
                <td>
                    <label class="sr-only" for="date1">Laatste contact</label>
                    <select class="form-control" id="lastcontact1" name="dateOfLastExposure[]">
                        <option disabled selected>Selecteer</option>
                        @for ($i = 0; $i < 14; $i++)
                            <?php
                            $date = Date::parse("-$i days")->format("Y-m-d");
                            $label = Date::parse("-$i days")->format('l j M');
                            ?>
                            <option value="{{ $date }}">{{ $label }}</option>
                        @endfor
                    </select>
                </td>
                <td>
                    <label class='sr-only' for="informeren1">Wie informeert</label>
                    <select class="form-control" id="informeren1" name="communication[]">
                        <option disabled selected>Selecteer</option>
                        <option>GGD</option>
                        <option>Index</option>
                    </select>
                </td>
                <td class="text-center">
                    <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
                </td>
            </tr>

            </tbody>

        </table>
        <!-- End of table component -->

        <!-- Start of table title component -->
        <div class="align-items-end  mb-3 mt-3">
            <h3 class="mb-0"><div class="question-nr">4</div> Vertel de index welke app ze moeten downloaden</h3>
            <p class="mt-2 mb-0  ml-auto">De index heeft een app nodig die ze kunnen downloaden in de Play of AppStore waarmee ze de gegevens op een veilige manier met de GGD kunnen delen.</p>
        </div>
        <!-- End of table title component -->

        <!-- Start of table title component -->
        <div class="align-items-end  mb-3 mt-3">
            <h3 class="mb-0"><div class="question-nr">5</div> Deel de code met de index</h3>
            <p class="mt-2 mb-0  ml-auto">Met deze code heeft de index toegang tot de contacten uit de aanleverlijst.</p>
        </div>
        <!-- End of table title component -->
        <div class="pairingcode">
            <span>{{ $case->pairingCode }}</span>
        </div>

        <div class="btn-group">
            <input type="submit" class="btn btn-primary" value="Case aanmaken" />
        </div>

    </form>

</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
