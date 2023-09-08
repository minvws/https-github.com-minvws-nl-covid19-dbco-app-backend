<p>
@if ($hasSymptoms)
    Sie haben sich mit Covid-19 infiziert. Um zu verhindern, dass sich die Menschen um Sie herum ebenfalls anstecken, bittet das GGD Sie, sich an diese Verhaltensregeln zu halten:
@else
    Sie haben sich mit Covid-19 infiziert. Auch wenn Sie sich gesund fühlen, können Sie infiziert sein und das Virus an Dritte weitergeben. Daher bittet das GGD Sie, sich an diese Verhaltensregeln zu halten:
@endif
</p>

<ul>
    @if ($hasSymptoms)
        @if ($contagiousPeriodEndDate == $longestIsolationPeriodEndDate)
            <li><strong>Bleiben Sie bis zum {{ @toDate($longestIsolationPeriodEndDate) }} zuhause und empfangen Sie keinen Besuch.</strong>
                Ab dem {{ @toDate($longestIsolationPeriodEndDate, '+1') }} dürfen Sie das Haus wieder verlassen.</li>
        @else
            <li><strong>Bleiben Sie bis zum {{ @toDate($contagiousPeriodEndDate) }} zuhause und empfangen Sie keinen Besuch.</strong>
            Verlassen Sie das Haus erst wieder, wenn Sie mindestens 24 Stunden beschwerdefrei sind. Typische Beschwerden sind Fieber, Husten, Halsschmerzen und Schnupfen.
            Wenn die Beschwerden nicht aufhören, dürfen Sie am {{ @toDate($longestIsolationPeriodEndDate, '+1') }} das Haus wieder verlassen.</li>
        @endif
    @else
        <li><strong>Bleiben Sie bis zum {{ @toDate($contagiousPeriodEndDate) }} zuhause und empfangen Sie keinen Besuch.</strong></li>
    @endif
    @if ($hasHouseMates)
        <li>Vermeiden Sie jeglichen Kontakt zu Ihren Mitbewohnern. Bleiben Sie in Ihrem Zimmer und schlafen Sie dort auch allein. Ihre Mitbewohner sollten das Zimmer nicht betreten.</li>
    @endif
    <li>Vermeiden Sie bis zum {{ @toDate($longestIsolationPeriodEndDate) }} jeglichen Kontakt zu gefährdeten Personen.</li>
    @if (!$receivesExtensiveContactTracing)
        <li>Warnen Sie Personen, zu denen Sie Kontakt hatten zwischen dem {{ @toDate($contagiousPeriodStartDate) }} und dem Zeitpunkt, zu dem Sie sich in Isolierung begeben haben. Verweisen Sie diese Personen auf <a href="https://mijnvraagovercorona.nl/">mijnvraagovercorona.nl</a>.</li>
    @endif
    @if ($isEduDaycare)
        <li>Informieren Sie die Kita, die Grundschule oder weiterführende Schule über Ihre Erkrankung.</li>
    @endif
    @if ($isHealthProfessional)
        <li>Sind Sie im Gesundheitswesen beschäftigt? Dann halten Sie Rücksprache mit Ihrem Betriebsarzt oder Arbeitgeber, ob Sie arbeiten dürfen.</li>
    @endif
    <li>Waschen Sie Ihre Hände regelmäßig mit Wasser und Seife, husten und niesen Sie in die Armbeuge.</li>
</ul>
@if (!$hasSymptoms)
    <p><strong>Sie haben derzeit keine Beschwerden. Treten zu einem späteren Zeitpunkt Beschwerden auf?</strong></p>
    <ul>
        <li>Dann bleiben Sie zuhause und verlassen Sie das Haus erst wieder, wenn seit Beginn der Beschwerden 5 Tage vergangen sind und Sie mindestens 24 Stunden lang beschwerdefrei sind.
            Typische Beschwerden sind Fieber, Husten, Halsschmerzen und Schnupfen.
            Haben Sie nach 5 Tagen noch Beschwerden? Dann bleiben Sie zuhause, bis Sie mindestens 24 Stunden lang beschwerdefrei sind, aber maximal 10 Tage lang nach Beginn der Beschwerden.</li>
        <li>Vermeiden Sie 10 Tage lang nach Beginn der Beschwerden jeglichen Kontakt zu gefährdeten Personen.</li>
    </ul>
@endif
