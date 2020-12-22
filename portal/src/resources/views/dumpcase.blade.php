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
<textarea id="clipboard" class="clipboard-offscreen" aria-hidden="true" ></textarea>

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
                        Gegevens case
                        <div class="float-right">
                            <button data-copyvalue="{{ $copydata['case'] }}" class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row copyable"
                             data-copyvalue="{{ $case->name }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="name">
                            <div class="col col-4">Naam</div>
                            <div class="col">
                                {{ $case->name }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('name', $copiedFields))
                                            &check;
                                        @endif</span>
                                </div>

                            </div>
                        </div>
                        <div class="row copyable"
                             data-copyvalue="{{ $case->caseId }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="caseid">
                            <div class="col col-4">HPZone casenummer</div>
                            <div class="col">
                                {{ $case->caseId }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('caseid', $copiedFields))
                                        &check;
                                        @endif</span>
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
                            <button data-copyvalue="{{ $copydata['user'] }}" class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row copyable"
                             data-copyvalue="{{ $user->name }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="username">
                            <div class="col col-4">Naam (volledig)</div>
                            <div class="col">
                                {{ $user->name }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('username', $copiedFields))
                                        &check;
                                        @endif</span>
                                </div>

                            </div>
                        </div>
                        <div class="row copyable"
                             data-copyvalue="{{ $case->createdAt->format("Y-m-d") }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="casecreated">
                            <div class="col col-4">Datum contactonderzoek</div>
                            <div class="col">
                                {{ $case->createdAt->format('d-m-Y (l)') }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('casecreated', $copiedFields))
                                        &check;
                                        @endif</span>
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
                            <button data-copyvalue="{{ $copydata['index'] }}" class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                        </div>
                    </h5>
                    <div class="container">
                        <div class="row copyable"
                             data-copyvalue="{{ $case->dateOfSymptomOnset->format("Y-m-d") }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="dateofsymptomonset">
                            <div class="col col-4">Datum eerste ziektedag (EZD)</div>
                            <div class="col">
                                {{ $case->dateOfSymptomOnset->format('d-m-Y (l)') }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('dateofsymptomonset', $copiedFields))
                                        &check;
                                        @endif</span>
                                </div>
                            </div>
                        </div>
                        <div class="row copyable"
                             data-copyvalue="{{ $case->calculateContagiousPeriodStart()->format('Y-m-d') }}"
                             data-case="{{$case->uuid}}"
                             data-copyfield="contagiousperiodstart"
                        >
                            <div class="col col-4">Datum start besmettelijke periode</div>
                            <div class="col">
                                {{ $case->calculateContagiousPeriodStart()->format('d-m-Y (l)') }}
                                <div class="float-right">
                                    <span class="row-action copy">Kopieer</span>
                                    <span class="row-status">@if (in_array('contagiousperiodstart', $copiedFields))
                                        &check;
                                        @endif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end of section -->
            <!-- Section: Contactonderzoek -->
            @foreach ($taskcategories as $category => $tasks)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ $groupTitles[$category]['title'] }}
                            <div class="float-right">
                                <button data-copyvalue="{{ $copydata['contacts'][$category] }}" class="copy-card-values btn btn-outline-primary btn-sm">Kopieer deze gegevens</button>
                            </div>
                        </h5>
                        <div class="container">
                            @foreach ($tasks as $task)
                                <div class="case-task">
                                    @foreach ($task['data'] as $key => $value)
                                        <div class="row copyable"
                                             data-copyvalue="{{ $value->copyValue ?? '-' }}"
                                             data-case="{{$case->uuid}}"
                                             data-task="{{ $task['uuid']->value }}"
                                             data-copyfield="{{ $key }}">
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
                                                    <span class="row-status">@if (in_array($key, $task['copiedFields']))
                                                          &check;
                                                        @endif</span>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="row copyable"
                                         data-copyvalue="{{ $task['context']->copyValue ?? '-' }}"
                                         data-case="{{$case->uuid}}"
                                         data-task="{{ $task['uuid']->value }}"
                                         data-copyfield="context">
                                        <div class="col-4">
                                            Toelichting
                                        </div>
                                        <div class="col">
                                            {{ $task['context']->displayValue ?? '-' }}
                                            <div class="float-right">
                                                <span class="row-action copy">Kopieer</span>
                                                <span class="row-status">@if (in_array('context', $task['copiedFields']))
                                                    &check;
                                                    @endif</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row copyable"
                                         data-copyvalue="{{ $task['dateoflastexposure']->copyValue ?? '-' }}"
                                         data-case="{{$case->uuid}}"
                                         data-task="{{ $task['uuid']->value }}"
                                         data-copyfield="dateoflastexposure">
                                        <div class="col-4">
                                            Laatste contactmoment
                                        </div>
                                        <div class="col">
                                            {{ $task['dateoflastexposure']->displayValue ?? '-' }}
                                            <div class="float-right">
                                                <span class="row-action copy">Kopieer</span>
                                                <span class="row-status">@if (in_array('dateoflastexposure', $task['copiedFields']))
                                                    &check;
                                                        @endif</span>
                                            </div>
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
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="invisible">
                <input type="text" size="10" id="remote_{{ $case->uuid }}"
                       value="{{ $case->exportId }}"/>
                <input type="checkbox" class="chk-case-upload-completed"
                       id="upload_{{ $case->uuid }}"/>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
