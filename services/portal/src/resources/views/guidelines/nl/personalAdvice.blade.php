<p>
@if ($hasSymptoms)
    U heeft corona. Om te voorkomen dat u het virus doorgeeft aan anderen vraagt de GGD u om zich aan deze adviezen te houden:
@else
    U heeft corona. Ook als u zich niet ziek voelt, kunt u het virus gemakkelijk doorgeven aan anderen. De GGD vraagt u daarom om zich aan deze adviezen te houden:
@endif
</p>

<ul>
    @if ($hasSymptoms)
        @if ($contagiousPeriodEndDate == $longestIsolationPeriodEndDate)
            <li><strong>Blijf tot en met {{ @toDate($longestIsolationPeriodEndDate) }} thuis en ontvang geen bezoek.</strong>
                U mag op {{ @toDate($longestIsolationPeriodEndDate, '+1') }} weer naar buiten.</li>
        @else
            <li><strong>Blijf tot en met {{ @toDate($contagiousPeriodEndDate) }} thuis en ontvang geen bezoek.</strong>
                Ga daarna pas weer naar buiten als u minimaal 24 uur geen klachten meer heeft. Klachten zijn koorts, hoesten, keelpijn en neusverkoudheid.
                Als de klachten niet overgaan mag u op {{ @toDate($longestIsolationPeriodEndDate, '+1') }} weer naar buiten.</li>
        @endif
    @else
        <li><strong>Blijf tot en met {{ @toDate($contagiousPeriodEndDate) }} thuis en ontvang geen bezoek.</strong></li>
    @endif
    @if ($hasHouseMates)
        <li>Vermijd contact met uw huisgenoten. Blijf op een eigen kamer en slaap daar ook. Laat uw huisgenoten niet op deze kamer komen.</li>
    @endif
    <li>Blijf tot en met {{ @toDate($longestIsolationPeriodEndDate) }} uit de buurt van kwetsbare personen.</li>
    @if (!$receivesExtensiveContactTracing)
        <li>Waarschuw mensen met wie u contact had tussen {{ @toDate($contagiousPeriodStartDate) }} en het moment dat u in isolatie ging. Verwijs deze mensen door naar <a href="https://mijnvraagovercorona.nl/">mijnvraagovercorona.nl</a>.</li>
    @endif
    @if ($isEduDaycare)
        <li>Laat de kinderopvang, basisschool of middelbare school weten dat u bent besmet.</li>
    @endif
    @if ($isHealthProfessional)
        <li>U werkt in de zorg. Overleg met uw bedrijfsarts of werkgever of u kunt werken.</li>
    @endif
    <li>Was regelmatig uw handen met water en zeep, hoest en nies in uw elleboog.</li>
</ul>
@if (!$hasSymptoms)
    <p><strong>U heeft nu geen klachten. Krijgt u alsnog klachten?</strong></p>
    <ul>
        <li>Blijf dan thuis en ga pas weer naar buiten als er 5 dagen voorbij zijn sinds het begin van de klachten én de klachten minimaal 24 uur over zijn.
            Klachten zijn koorts, hoesten, keelpijn en neusverkoudheid. Heeft u na 5 dagen nog wel klachten?
            Blijf dan thuis tot u minimaal 24 uur geen klachten meer heeft, en maximaal tot en met 10 dagen na het begin van uw klachten.</li>
        <li>Blijf tot en met 10 dagen na het begin van uw klachten uit de buurt van kwetsbare personen.</li>
    </ul>
@endif
