<x-layout>
<x-slot name="title">
    Case overzicht
</x-slot>
<div id="app" class="container-xl">
    @include('navbar', ['root' => true])
    <div class="row">
        <div class="col ml-5 mr-5">
            <!-- Start of page title component -->
            <h2 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                <span class="font-weight-bold">@if ($isPlanner) Cases @else Mijn Cases @endif</span>

                <!-- End of page title component -->

                <!-- Start of add button component -->
                <span class="ml-auto">
                    <a href="{{ route('case-new') }}" class="btn  btn-primary  ml-auto">
                        Nieuwe case
                    </a>
                </span>
            <!-- End of add button component -->
            </h2>
            <!-- Start of tabs component -->
            @if ($isPlanner)
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
            @endif
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
                      <!-- Start of table component -->
                    <covid-case-table-component :is-planner="{{ $isPlanner ? "true" : "false" }}" filter="mine"></covid-case-table-component>
                </div>
                @if ($isPlanner)
                    <div class="tab-pane  fade" id="nav-all-cases" role="tabpanel" aria-labelledby="nav-all-cases-tab">
                        <!-- Start of table component -->
                        <covid-case-table-component :is-planner="{{ $isPlanner ? "true" : "false" }}" filter="all"></covid-case-table-component>
                    </div>
                @endif
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
                <form autocomplete="off">
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

</x-layout>

