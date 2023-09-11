@php

    /**
     * @var \App\Models\CovidCase $case
     * @var \App\Models\CovidCase\Contacts $contacts
     * @var \App\Models\CovidCase\Deceased $deceased
     * @var \App\Models\CovidCase\EduDaycare $eduDaycare
     * @var \App\Models\CovidCase\ExtensiveContactTracing $extensiveContactTracing
     * @var \App\Models\CovidCase\Hospital $hospital
     * @var \App\Models\CovidCase\Index $index
     * @var \App\Models\CovidCase\Job $job
     * @var \App\Models\CovidCase\Communication $communication
     * @var \App\Models\CovidCase\Medication $medication
     * @var \App\Models\CovidCase\Pregnancy $pregnancy
     * @var \App\Models\CovidCase\PrincipalContextualSettings $principalContextualSettings
     * @var \App\Models\CovidCase\RecentBirth $recentBirth
     * @var \App\Models\CovidCase\Symptoms $symptoms
     * @var \App\Models\CovidCase\Immunity $immunity
     * @var \App\Models\CovidCase\Test $test
     * @var \App\Models\CovidCase\UnderlyingSuffering $underlyingSuffering
     * @var \Illuminate\Support\Collection $tasks
     * @var \Illuminate\Support\Collection $contexts
     * @var \App\Models\CovidCase\Job $job
     */

@endphp

<table border="1">
    <tbody>
        <tr>
            <td>Aanmaakdatum in BCO Portaal</td>
            <td>{{ $case->createdAt !== null ? \App\Helpers\TimezoneAware::format($case->createdAt, 'd-m-Y H:i') : 'Onbekend' }}</td>
        </tr>
        <tr>
            <td>Fase BCO:</td>
            <td>{{ $case->bcoPhase->label ?? 'Onbekend' }}</td>
        </tr>
        @if(@$extensiveContactTracing instanceof \App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV1UpTo1)
            <tr>
                <td>Uitgebreid BCO</td>
                <td>
                    @if ($extensiveContactTracing->receivesExtensiveContactTracing === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                        Ja
                        @if (collect($extensiveContactTracing->reasons)->count() > 0)
                            , {{ collect($extensiveContactTracing->reasons)->pluck('label')->implode(', ') }}
                        @endif
                        @if ($extensiveContactTracing->notes)
                            <br>
                        @endif
                        {{ $extensiveContactTracing->notes ? 'Toelichting: ' . $extensiveContactTracing->notes : '' }}
                    @elseif ($extensiveContactTracing->receivesExtensiveContactTracing === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                        Nee
                    @else
                        Onbekend
                    @endif
                </td>
            </tr>
        @endif
        @if(@$extensiveContactTracing instanceof \App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2Up)
            @include('copy.shared.extensive-contact-tracing', ['extensiveContactTracing' => $extensiveContactTracing])
        @endif
        <tr>
            <td>Persoon:</td>
            <td>
                Leeftijd: {{ $index->dateOfBirth ? \Carbon\CarbonImmutable::parse($index->dateOfBirth)->age . ' jaar' : '' }}<br>
                EZD: {{ $test->dateOfSymptomOnset ? $test->dateOfSymptomOnset->format('d-m-Y') . ' (' . \Carbon\CarbonImmutable::parse($test->dateOfSymptomOnset)->dayName . ')' : '' }}
                @if ($symptoms->hasSymptoms === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    (Symptomatisch)
                @elseif ($symptoms->hasSymptoms === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    (Asymptomatisch)
                @else
                    (Onbekend)
                @endif
                <br>
                @if ($symptoms->hasSymptoms && $symptoms->hasSymptoms === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Klachten: {{ collect($symptoms->symptoms)->pluck('label')->merge((array) $symptoms->otherSymptoms)->implode(', ') }}<br>
                    @if ($symptoms instanceof SymptomsV1UpTo1)
                    @if ($symptoms->wasSymptomaticAtTimeOfCall === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                        Klachten zijn nog aanwezig<br>
                    @elseif ($symptoms->wasSymptomaticAtTimeOfCall === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                        Klachten zijn over<br>
                        Laatste dag van klachten: {{ $symptoms->stillHadSymptomsAt ? $symptoms->stillHadSymptomsAt->format('d-m-Y') : 'onbekend' }}<br>
                    @else
                        Onbekend<br>
                    @endif
                    @endif
                    @if (!empty(trim($symptoms->diseaseCourse)))
                        Ziekteverloop: {{ $symptoms->diseaseCourse }}<br>
                    @endif
                @else
                    Klachten: {{ $symptoms->hasSymptoms ? $symptoms->hasSymptoms->label : 'Onbekend' }}<br>
                @endif
                Testdatum: {{ $test->dateOfTest ? $test->dateOfTest->format('d-m-Y') . ' (' . \Carbon\CarbonImmutable::parse($test->dateOfTest)->dayName . ')' : '' }}<br>
                @if ($test->reasons || $test->otherReason)
                    Reden voor test: {{ collect($test->reasons)->pluck('label')->merge((array) $test->otherReason)->implode(', ') }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Bijzonderheden klinisch beloop:</td>
            <td>
                @if ($deceased->isDeceased === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Overleden: Ja, {{ $deceased->deceasedAt ? $deceased->deceasedAt->format('d-m-Y') : 'onbekende datum' }}{{ $deceased->cause ? sprintf(', %s', $deceased->cause->label) : '' }}
                    <br>
                @endif
                @if ($pregnancy->isPregnant === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    @if ($pregnancy instanceof \App\Models\Versions\CovidCase\Pregnancy\PregnancyV1UpTo1)
                        Zwanger: Ja, uitgerekende datum {{ $pregnancy->dueDate ? $pregnancy->dueDate->format('d-m-Y') : 'onbekend' }}
                    @endif
                    @if ($pregnancy instanceof \App\Models\Versions\CovidCase\Pregnancy\PregnancyV2Up)
                        Zwanger: Ja{{ $pregnancy->remarks ? sprintf(', %s', $pregnancy->remarks) : '' }}
                    @endif
                    <br>
                @endif
                @if ($recentBirth instanceof \App\Models\Versions\CovidCase\RecentBirth\RecentBirthV1UpTo1 && $recentBirth->hasRecentlyGivenBirth === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Recent bevallen: Ja, {{ $recentBirth->birthDate ? $recentBirth->birthDate->format('d-m-Y') : 'datum onbekend' }}, {{ $recentBirth->birthRemarks }}
                    <br>
                @endif
                @if ($hospital->isAdmitted !== null && $hospital->isAdmitted === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Ziekenhuisopname: Ja, Reden van opname: {{ $hospital->reason ? $hospital->reason->label : 'Onbekend' }}@if ($hospital->admittedAt || $hospital->releasedAt), {{ $hospital->admittedAt ? $hospital->admittedAt->format('d-m-Y') : '?' }} tot {{ $hospital->releasedAt ? $hospital->releasedAt->format('d-m-Y') : '?' }}@endif{{ $hospital->name ? sprintf(', %s', $hospital->name) : '' }}{{ $hospital->location ? sprintf(', %s', $hospital->location) : '' }}
                    <br>
                @endif
                @if ($hospital->isAdmitted !== null && $hospital->isAdmitted === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes() && $hospital->isInICU !== null && $hospital->isInICU === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    IC Opname: Ja, {{ $hospital->admittedInICUAt ? $hospital->admittedInICUAt->format('d-m-Y') : 'datum onbekend' }}
                    <br>
                @endif
                @if ($underlyingSuffering->hasUnderlyingSuffering === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Onderliggend lijden: Ja, {{ collect($underlyingSuffering->items)->pluck('label')->merge($underlyingSuffering->otherItems ?? [])->map(function ($item) {
                        return trim(explode('(', $item)[0]);
                    })->implode(', ') }}<br>
                @endif
                @if ($underlyingSuffering->hasUnderlyingSuffering === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes() && $underlyingSuffering->remarks)
                    Toelichting onderliggend lijden: {{ sprintf(', %s', $underlyingSuffering->remarks) }}<br><br>
                @endif
                @if ($test->isReinfection === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())

                    Eerder besmet geweest: Ja
                    @if ($test->previousInfectionDateOfSymptom !== null && $test->dateOfTest !== null)
                        @php
                            $previousInfectionDateOfSymptom = \Carbon\CarbonImmutable::parse($test->previousInfectionDateOfSymptom)->setTime(0,0,0);
                            $dateOfTest = \Carbon\CarbonImmutable::parse($test->dateOfTest)->setTime(0,0,0);
                            $weeks = $previousInfectionDateOfSymptom->diffInWeeks($dateOfTest);
                            $days = $previousInfectionDateOfSymptom->diffInDays($dateOfTest) - (7 * $weeks);
                        @endphp
                        @if ($weeks > 0){{ $weeks }} @choice('week|weken', $weeks){{ $days > 0 ? ',' : ''}}@endif
                        @if ($days > 0){{ $days }} @choice('dag|dagen', $days)@endif
                        @if ($weeks > 0 || $days > 0)voor testdatum huidige case @endif
                    @endif
                    <br>
                    @if (is_bool($test->previousInfectionSymptomFree))
                        Klachtenvrij in tussentijd: {{ $test->previousInfectionSymptomFree ? 'Ja' : 'Nee' }}<br>
                    @else
                        Klachtenvrij in tussentijd: Onbekend<br>
                    @endif
                @elseif ($test->isReinfection === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    Eerder besmet geweest: Nee<br>
                @else
                    Eerder besmet geweest: Onbekend<br>
                @endif
            </td>
        </tr>
        <tr>
            <td>Verminderde afweer:</td>
            <td>
                @if ($underlyingSuffering->hasUnderlyingSufferingOrMedication === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes() && $medication->isImmunoCompromised === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Ja{{ $medication->immunoCompromisedRemarks ? sprintf(', %s', $medication->immunoCompromisedRemarks) : '' }}
                @elseif ($underlyingSuffering->hasUnderlyingSufferingOrMedication === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    Nee
                @elseif ($underlyingSuffering->hasUnderlyingSufferingOrMedication === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes() && $medication->isImmunoCompromised === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    Nee
                @else
                    Onbekend
                @endif
            </td>
        </tr>
        <tr>
            <td>Symptomatisch of asymptomatisch:</td>
            <td>
                @if ($symptoms->hasSymptoms && $symptoms->hasSymptoms === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Symptomatisch
                @elseif ($symptoms->hasSymptoms && $symptoms->hasSymptoms === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    Asymptomatisch
                @else
                    Onbekend
                @endif
            </td>
        </tr>
        @php
            $workContexts = $contexts->whereIn('relationship.value', ['staff', 'teacher', 'student']);
            $sectors = collect($job->sectors);
        @endphp
        <tr>
            <td>
                Werk, functie, stage, vrijwilligerswerk:<br>
                Gewerkt tijdens besmettelijke periode?
                @if ($workContexts->count() > 0)
                    Zo ja: welke werkplek/school en op welke data:
                @endif
            </td>
            <td>
                @if ($job->wasAtJob === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Ja,<br>
                    Sector werk: {{ $sectors->isNotEmpty() ? $sectors->pluck('label')->implode(', ') : 'Onbekend' }}<br>
                    @if ($job->professionCare && $sectors->contains('group.value', 'care'))
                        Beroep: {{ $job->professionCare->label }}<br>
                    @endif
                    @if ($job->particularities)
                        Bijzonderheden: {{ $job->particularities }}<br>
                    @endif
                @elseif ($job->wasAtJob === \MinVWS\DBCO\Enum\Models\YesNoUnknown::no())
                    Nee<br>
                @else
                    Onbekend<br>
                @endif
                @if ($workContexts->count() > 0)
                    @foreach($workContexts as $context)
                        {{ $context['relationship'] }} bij {{ $context['label'] ?? 'onbekend' }}<br>
                        Afdeling(en): {{ implode(', ', (array) $context['sections']) }}<br>
                        Aanwezig in de bronperiode:<br>
                        @if ($context['moments']->where('source', '=', true)->isNotEmpty())
                            @foreach ($context['moments']->where('source', '=', true) as $moment)
                                {{ $moment->formatted }}<br>
                            @endforeach
                        @else
                            n.v.t.<br>
                        @endif
                        Aanwezig in de besmettelijke periode:<br>
                        @if ($context['moments']->where('source', '=', false)->isNotEmpty())
                            @foreach ($context['moments']->where('source', '=', false) as $moment)
                                {{ $moment->formatted }}<br>
                            @endforeach
                        @else
                            n.v.t.<br>
                        @endif

                        @if (!$loop->last)
                            <br>
                        @endif
                    @endforeach
                @endif
            </td>
        </tr>
        <tr>
            <td>Contactonderzoek:</td>
            <td>
                @php
                    if ($contacts instanceof \App\Models\Versions\CovidCase\Contacts\ContactsV2Up && $contacts->estimatedMissingContacts === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes()) {
                        $cat1Count = $contacts->estimatedCategory1Contacts;
                        $cat2Count = $contacts->estimatedCategory2Contacts;
                    } else {
                        $cat1Count = $tasks->filter(function ($task) { return $task->category === '1'; })->count();
                        $cat2Count = $tasks->filter(function ($task) { return in_array($task->category, ['2a', '2b'], true); })->count();
                    }

                    $cat3Count = $tasks->filter(function ($task) { return in_array($task->category, ['3', '3a', '3b'], true); })->count();
                @endphp
                Aantal huisgenoten: {{ $cat1Count }}<br>
                Aantal overige nauwe contacten: {{ $cat2Count }}<br>
                @if ($contacts instanceof \App\Models\Versions\CovidCase\Contacts\ContactsV2Up && $contacts->estimatedMissingContacts === \MinVWS\DBCO\Enum\Models\YesNoUnknown::yes())
                    Aantal overige contacten: {{ $contacts->estimatedCategory3Contacts }}<br>
                @endif
            </td>
        </tr>
        <tr>
            <td>Clustering: Vermeld de naam van alle contexten en situations:</td>
            <td>
                @if ($contexts->isNotEmpty() || ($principalContextualSettings && $principalContextualSettings->hasPrincipalContextualSettings === true))
                    {{ collect($contexts)->pluck('label')->filter()->implode(', ') }}<br>
                    @if ($principalContextualSettings && $principalContextualSettings->hasPrincipalContextualSettings === true)
                        <br>
                        {{ collect($principalContextualSettings->items)->merge($principalContextualSettings->otherItems)->implode(',') }}<br>
                    @endif
                @else
                    n.v.t<br>
                @endif
            </td>
        </tr>
        <tr>
            <td>Bijzonderheden:</td>
            <td>{{ $communication->particularities ?? 'n.v.t.' }}</td>
        </tr>
        <tr>
            <td>Afgesproken beleid: voor index, excl. leefregels:</td>
            <td>
                @if ($communication->isolationAdviceGiven && count($communication->isolationAdviceGiven) > 0)
                    @foreach($communication->isolationAdviceGiven as $isolationAdvice)
                        - {{ $isolationAdvice->label }}<br/>
                    @endforeach
                @endif
                @if ($communication->conditionalAdviceGiven)
                    - {{ $communication->conditionalAdviceGiven }}<br/>
                @endif
                @if ($communication->otherAdviceGiven)
                    @if ($communication->conditionalAdviceGiven || ($communication->isolationAdviceGiven && count($communication->isolationAdviceGiven) > 0))
                        <br/>
                    @endif
                    Andere gegeven adviezen:<br/>
                    {{ $communication->otherAdviceGiven }}<br/>
                @endif
            </td>
        </tr>
    </tbody>
</table>

