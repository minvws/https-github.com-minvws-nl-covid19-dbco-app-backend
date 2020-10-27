<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Cases</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body>

<div class="container-xl">

    <!-- Start of navbar component -->
    <nav class="navbar  navbar-expand-lg  navbar-light  bg-transparent  pl-0  pr-0  w-100">
        <button class="navbar-toggler  ml-auto  bg-white"
                type="button"
                data-toggle="collapse"
                data-target="#navbarToggler"
                aria-controls="navbarToggler"
                aria-expanded="false" aria-label="Navigatie tonen">
            <span class="navbar-toggler-icon"></span>
        </button>

        @include('identitybar')
    </nav>
    <!-- End of navbar component -->

    <!-- Start of page title component -->
    <h1 class="mt-4  mb-4">Cases</h1>
    <!-- End of page title component -->

    <!-- Start of add button component -->
    <nav class="nav  mb-2">
        <a href="/newcase" class="btn  btn-primary  ml-auto">
            Case openen
        </a>
    </nav>
    <!-- End of add button component -->

    <!-- Start of tabs component -->
    <nav>
        <div class="nav  nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item  nav-link  active"
               id="nav-own-cases-tab"
               data-toggle="tab"
               href="#nav-own-cases"
               role="tab"
               aria-controls="nav-own-cases"
               aria-selected="true">Mijn cases</a>
<!-- disabled because not yet functional
            <a class="nav-item nav-link"
               id="nav-all-cases-tab"
               data-toggle="tab"
               href="#nav-all-cases"
               role="tab"
               aria-controls="nav-all-cases"
               aria-selected="false">Alle cases</a>
     end disabled -->
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
            <!-- Start of table component -->
            <table class="table  table-rounded  table-hover  table-ggd">
                <colgroup>
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                </colgroup>
                <thead>
                <tr>
                    <th scope="col">Naam</th>
                    <th scope="col">Casenr.</th>
                    <th scope="col">Eerste ziektedag</th>
                    <th scope="col">Status</th>
                    <th scope="col">Laatst bewerkt</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cases as $case)
                    <tr role="button" class="custom-link clickable-row" data-href="/{{ $case->editCommand }}/{{ $case->uuid }}">
                        <th scope="row">{{ $case->name }}</th>
                        <td>{{ $case->caseId }}</td>
                        <td>{{ $case->dateOfSymptomOnset != NULL ? $case->dateOfSymptomOnset->format('l j M') : '' }}</td>
                        <td>{{ $case->status }}</td>
                        <td>{{ $case->updatedAt->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </table>
            <!-- End of table component -->
        </div>

        <div class="tab-pane  fade" id="nav-all-cases" role="tabpanel" aria-labelledby="nav-all-cases-tab">
            <!-- Start of table component -->
            <table class="table  table-rounded  table-has-header  table-hover  table-ggd">
                <colgroup>
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                    <col class="w-20">
                </colgroup>
                <thead>
                <tr>
                    <th scope="col">Naam index</th>
                    <th scope="col">Casenr.</th>
                    <th scope="col">Bco-er</th>
                    <th scope="col">Datum test</th>
                    <th scope="col">Laatst bewerkt</th>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <th scope="row">Tobias van Geijn</th>
                    <td>AMS/C20. 354921</td>
                    <td>Mathijs Groense</td>
                    <td>ma 5 okt</td>
                    <td>7 uur geleden</td>
                </tr>
                <tr>
                    <th scope="row">Emiel Janson</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>11 minuten geleden</td>
                </tr>
                <tr>
                    <th scope="row">Lia Bardoel</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>ma 5 okt</td>
                    <td>2 uur geleden</td>
                </tr>
                <tr>
                    <th scope="row">Joris Leker</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>51 minuten geleden</td>
                </tr>
                <tr>
                    <th scope="row">Emiel Janson</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>11 minuten geleden</td>
                </tr>
                <tr>
                    <th scope="row">Lia Bardoel</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>ma 5 okt</td>
                    <td>2 uur geleden</td>
                </tr>
                <tr>
                    <th scope="row">Joris Leker</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>51 minuten geleden</td>
                </tr>
                <tr>
                    <th scope="row">Emiel Janson</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>11 minuten geleden</td>
                </tr>
                <tr>
                    <th scope="row">Lia Bardoel</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>ma 5 okt</td>
                    <td>2 uur geleden</td>
                </tr>
                <tr>
                    <th scope="row">Joris Leker</th>
                    <td>7593067-BA</td>
                    <td>Mathijs Groense</td>
                    <td>Zo 4 okt</td>
                    <td>51 minuten geleden</td>
                </tr>
            </table>
            <!-- End of table component -->
        </div>
    </div>
    <!-- End of tabs component -->
</div>

<!--------------------------
  START OF MODALS
---------------------------->

<!-- Start of create case modal (deprecated, replaced by /newcase form) -->
<div class="modal fade" id="createCaseModal" tabindex="-1" aria-labelledby="createCaseModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header  border-bottom-0  pb-0">
                <h5 class="modal-title">Case toevoegen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Sluiten">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="name" class="col-form-label">Casenaam</label>
                        <input type="text" class="form-control" id="name" placeholder="Voer casenaam in">
                        <label for="caseid" class="col-form-label">Case ID</label>
                        <input type="text" class="form-control" id="caseId" placeholder="Voer casenummer in">
                    </div>
                </form>
            </div>
            <div class="modal-footer  border-top-0  pt-0">
                <button type="button" class="btn btn-primary  mr-auto" data-dismiss="modal">Case toevoegen</button>
            </div>
        </div>
    </div>
</div>
<!-- End of create case modal -->

</body>
</html>
