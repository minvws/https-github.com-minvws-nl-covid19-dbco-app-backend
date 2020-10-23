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

<div class="container-xl">

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

    <!-- Start of page title component -->
    <h1 class="mt-4  mb-4  font-weight-normal">
        Dhr <span class="font-weight-bold">Jeroen van Drouwelen</span> aanleverlijst
    </h1>
    <!-- End of page title component -->

    <!-- Start of table title component -->
    <div class="d-flex  align-items-end  mb-3">
        <h2 class="mb-0">Contacten</h2>
        <p class="mb-0  ml-auto">Velden met een <i class="icon  icon--eye"></i> zijn in de app zichtbaar voor de index</p>
    </div>
    <!-- End of table title component -->

    <!-- Start of table component -->
    <table class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-hover  table-form  table-ggd">
        <!--
            Modify the col definitions in the colgroup below to change the widths of the the columns.
            The w-* classes will be automatically generated based on the $sizes array which is defined in the scss/_variables.scss
        -->
        <colgroup>
            <col class="w-25">
            <col class="w-25">
            <col class="w-50">
            <col class="w-15">
            <col class="w-15">
            <col class="w-25">
            <col class="w-10">
        </colgroup>
        <thead>
        <tr>
            <th scope="col">Naam / toelichting <i class="icon  icon--eye"></i></th>
            <th scope="col">Context <i class="icon  icon--eye"></i></th>
            <th scope="col">Aard van het contact <i class="icon  icon--eye"></i></th>
            <th scope="col">Informeren</th>
            <th scope="col">Categorie</th>
            <th scope="col">Laatste contact</th>
            <th scope="col"></th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <th scope="row">Maartje</th>
            <td>
                <label class="sr-only" for="context1">Context</label>
                <input type="text" class="form-control" id="context1" value="Kraamvisite">
            </td>
            <td>
                <label class="sr-only" for="adres1">Aard van het contact</label>
                <input type="text" class="form-control" id="adres1" value="Ca 2 uur naast elkaar op kleine bank">
            </td>
            <td>
                <label class='sr-only' for="informeren1">Informeren</label>
                <select class="form-control" id="informeren1">
                    <option>Ja</option>
                    <option>Nee</option>
                </select>
            </td>
            <td>
                <label class='sr-only' for="categorie1">Categorie</label>
                <select class="form-control" id="categorie1">
                    <option>2a</option>
                    <option>2b</option>
                    <option>2c</option>
                </select>
            </td>
            <td>
                <label class="sr-only" for="date1">Laatste contact</label>
                <input class="form-control" type="date" id="date1" value="04/10/2020">
            </td>
            <td class="text-center">
                <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
            </td>
        </tr>
        <tr>
            <th scope="row">Zwager</th>
            <td>
                <label class="sr-only" for="context2">Context</label>
                <input type="text" class="form-control" id="context2" value="Kraamvisite">
            </td>
            <td>
                <label class="sr-only" for="adres2">Aard van het contact</label>
                <input type="text" class="form-control" id="adres2" value="Knuffel bij vertrek">
            </td>
            <td>
                <label class='sr-only' for="informeren2">Informeren</label>
                <select class="form-control" id="informeren2">
                    <option>Ja</option>
                    <option>Nee</option>
                </select>
            </td>
            <td>
                <label class='sr-only' for="categorie2">Categorie</label>
                <select class="form-control" id="categorie2">
                    <option>2a</option>
                    <option>2b</option>
                    <option>2c</option>
                </select>
            </td>
            <td>
                <label class="sr-only" for="date2">Laatste contact</label>
                <input id="date2" class="form-control" type="date" value="04/10/2020">
            </td>
            <td class="text-center">
                <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
            </td>
        </tr>
        <tr>
            <th scope="row">Oma V.</th>
            <td>
                <label class="sr-only" for="context3">Context</label>
                <input type="text" class="form-control" id="context3" value="Verjaardag oma">
            </td>
            <td>
                <label class="sr-only" for="adres3">Aard van het contact</label>
                <input type="text" class="form-control" id="adres3" value="Oma kwam telkens te dichtbij">
            </td>
            <td>
                <label class='sr-only' for="informeren3">Informeren</label>
                <select class="form-control" id="informeren3">
                    <option>Ja</option>
                    <option>Nee</option>
                </select>
            </td>
            <td>
                <label class='sr-only' for="categorie3">Categorie</label>
                <select class="form-control" id="categorie3">
                    <option>2a</option>
                    <option>2b</option>
                    <option>2c</option>
                </select>
            </td>
            <td>
                <label class="sr-only" for="date3">Laatste contact</label>
                <input id="date3" class="form-control" type="date" value="04/10/2020">
            </td>
            <td class="text-center">
                <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
            </td>
        </tr>
        <tr>
            <th scope="row">Familie leden <span class="font-weight-normal">(6)</span></th>
            <td>
                <label class="sr-only" for="context4">Context</label>
                <input type="text" class="form-control" id="context4" value="Verjaardag oma">
            </td>
            <td>
                <label class="sr-only" for="adres4">Aard van het contact</label>
                <input type="text" class="form-control" id="adres4" value="Met 6 andere familieleden in huiskamer oma">
            </td>
            <td>
                <label class='sr-only' for="informeren4">Informeren</label>
                <select class="form-control" id="informeren4">
                    <option>Ja</option>
                    <option>Nee</option>
                </select>
            </td>
            <td>
                <label class='sr-only' for="categorie4">Categorie</label>
                <select class="form-control" id="categorie4">
                    <option>2a</option>
                    <option>2b</option>
                    <option>2c</option>
                </select>
            </td>
            <td>
                <label class="sr-only" for="date4">Laatste contact</label>
                <input  id="date4" class="form-control" type="date" value="04/10/2020">
            </td>
            <td class="text-center">
                <button class="btn"><i class="icon  icon--delete  icon--m0"></i></button>
            </td>
        </tr>
        </tbody>

        <tfoot>
        <tr>
            <td colspan="7">
                <div class="btn-group">
                    <button class="btn  btn-link  pt-0  pb-0  pl-0">+ contact toevoegen</button>
                    <button class="btn  btn-link  pt-0  pb-0">+ groep toevoegen</button>
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
    <!-- End of table component -->

</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
