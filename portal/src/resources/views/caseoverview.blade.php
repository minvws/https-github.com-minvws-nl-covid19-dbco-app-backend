<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Cases</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<div class="container-xl">
    @include('navbar', ['root' => true])
    <div class="row">
        <div class="col ml-5 mr-5">
            <!-- Start of page title component -->
            <h2 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                <span class="font-weight-bold">Mijn Cases</span>

                <!-- End of page title component -->

                <!-- Start of add button component -->
                <span class="ml-auto">
                    <a href="/newcase" class="btn  btn-primary  ml-auto">
                        Nieuwe case
                    </a>
                </span>
            <!-- End of add button component -->
            </h2>
            <!-- Start of tabs component -->
            <!-- tabs disabled until we support multiple tabs
            <nav>
                <div class="nav  nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item  nav-link  active"
                       id="nav-own-cases-tab"
                       data-toggle="tab"
                       href="#nav-own-cases"
                       role="tab"
                       aria-controls="nav-own-cases"
                       aria-selected="true">Mijn cases</a>
                    <a class="nav-item nav-link"
                       id="nav-all-cases-tab"
                       data-toggle="tab"
                       href="#nav-all-cases"
                       role="tab"
                       aria-controls="nav-all-cases"
                       aria-selected="false">Alle cases</a>

                </div>
            </nav>
               end disabled -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
                    @if (count($cases) == 0)
                        <div class="bg-white text-center pt-5 pb-5">
                            Je hebt nog geen cases. Voeg deze toe door rechtsboven op de knop 'Nieuwe case' te drukken.
                        </div>
                    @else
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
                                    <th scope="row">{{ Str::limit($case->name, 30, '...') }}</th>
                                    <td>{{ Str::limit($case->caseId, 30, '...') }}</td>
                                    <td>{{ $case->dateOfSymptomOnset != NULL ? $case->dateOfSymptomOnset->format('l j M') : '' }}</td>
                                    <td>{{ $case->status }}</td>
                                    <td>{{ $case->updatedAt->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </table>
                        <!-- End of table component -->
                        {{ $cases->links() }}
                    @endif
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
    </div>
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
