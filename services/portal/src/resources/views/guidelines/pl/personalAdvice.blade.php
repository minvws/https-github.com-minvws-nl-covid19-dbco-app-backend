<p>
@if ($hasSymptoms)
    Mają Państwo koronawirusa. Aby nie zarazić nim innych osób, GGD (Gminna służba zdrowia) prosi Państwa o przestrzeganie poniższych zaleceń.
@else
    Mają Państwo koronawirusa. Nawet jeśli nie mają Państwo żadnych objawów choroby, mogą Państwo zarażać inne osoby, dlatego GGD (Gminna służba zdrowia) prosi o przestrzeganie poniższych zaleceń.
@endif
</p>

<ul>
    @if ($hasSymptoms)
        @if ($contagiousPeriodEndDate == $longestIsolationPeriodEndDate)
            <li><strong>Do dnia {{ @toDate($longestIsolationPeriodEndDate) }} proszę pozostać w domu i nie przyjmować żadnych gości.</strong>
                Dopiero w dniu {{ @toDate($longestIsolationPeriodEndDate, '+1') }} mogą Państwo ponownie wyjść z domu.</li>
        @else
            <li><strong>Do dnia {{ @toDate($contagiousPeriodEndDate) }} proszę pozostać w domu i nie przyjmować żadnych gości.</strong>
                Dopiero następnego dnia mogą Państwo opuścić dom, o ile przez ostatnie 24 godziny nie odczuwali już Państwo żadnych dolegliwości.
                Należą do nich: gorączka, kaszel, ból gardła i katar jak przy przeziębieniu.
                Jeśli dolegliwości nie ustąpiły, będą Państwo mogli wyjść z domu {{ @toDate($longestIsolationPeriodEndDate, '+1') }}.</li>
        @endif
    @else
        <li><strong>Do dnia {{ @toDate($contagiousPeriodEndDate) }} proszę pozostać w domu i nie przyjmować żadnych gości.</strong></li>
    @endif
    @if ($hasHouseMates)
        <li>Należy unikać kontaktu z innymi domownikami. Proszę więc pozostawać i sypiać w swoim pokoju. Inni domownicy nie powinni do niego wchodzić.</li>
    @endif
    <li>Do dnia {{ @toDate($longestIsolationPeriodEndDate) }} proszę unikać przebywania w pobliżu osób szczególnie narażonych na zakażenie.</li>
    @if (!$receivesExtensiveContactTracing)
        <li>Proszę powiadomić o swoim zakażeniu osoby, z którymi mieli Państwo kontakt pomiędzy {{ @toDate($contagiousPeriodStartDate) }} a dniem rozpoczęcia izolacji domowej. Proszę zalecić tym osobom, by zapoznały się z informacjami na stronie <a href="https://mijnvraagovercorona.nl/">mijnvraagovercorona.nl</a>.</li>
    @endif
    @if ($isEduDaycare)
        <li>Należy powiadomić żłobek, przedszkole, szkołę podstawową lub średnią, że są Państwo zakażeni.</li>
    @endif
    @if ($isHealthProfessional)
        <li>Jeśli pracują Państwo w służbie zdrowia, powinni Państwo porozmawiać ze swoim lekarzem zakładowym lub pracodawcą, czy mogą Państwo przyjść do pracy.</li>
    @endif
    <li>Proszę regularnie myć ręce wodą i mydłem. Kaszląc lub kichając należy zasłaniać usta/nos łokciem.</li>
</ul>
@if (!$hasSymptoms)
    <p><strong>Nie mają Państwo teraz żadnych dolegliwości. A jeśli pojawią się u Państwa w późniejszym czasie?</strong></p>
    <ul>
        <li>Proszę jeszcze pozostać w domu i wyjść z niego dopiero po upływie 5 dni od chwili, gdy po raz pierwszy wystąpiły u Państwa dolegliwości i pod warunkiem,
            że od 24 godzin nie odczuwają już Państwo żadnych dolegliwości. Należą do nich: gorączka, kaszel, ból gardła i katar jak przy przeziębieniu.
            Dolegliwości utrzymują się nadal po 5 dniach? Proszę pozostać w domu, dopóki się Państwo nie upewnią, że od 24 godzin nie odczuwają już Państwo żadnych dolegliwości.
            Izolacja domowa może potrwać do 10 dni od chwili, gdy po raz pierwszy wystąpiły u Państwa dolegliwości.</li>
        <li>Przez 10 dni od chwili pojawienia się u Państwa dolegliwości proszę unikać przebywania w pobliżu osób szczególnie narażonych na zakażenie.</li>
    </ul>
@endif
