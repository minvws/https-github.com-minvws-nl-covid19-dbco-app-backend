<p>
@if ($hasSymptoms)
    You have the coronavirus. The Municipal Public Health Service (GGD) would like you to comply with the following rules, so that you don’t pass the virus on to others:
@else
    You have the coronavirus. Even if you don’t feel ill, you can easily pass the virus on to others. That is why the Municipal Public Health Service (GGD) would like you to comply with the following rules:
@endif
</p>

<ul>
    @if ($hasSymptoms)
        @if ($contagiousPeriodEndDate == $longestIsolationPeriodEndDate)
            <li><strong>In all cases, stay at home up to and including {{ @toDate($longestIsolationPeriodEndDate) }} and do not receive visitors.</strong>
                You can go outside again on {{ @toDate($longestIsolationPeriodEndDate, '+1') }}.</li>
        @else
            <li><strong>In all cases, stay at home up to and including {{ @toDate($contagiousPeriodEndDate) }} and do not receive visitors.</strong>
                Then only go outside again if you have not had any symptoms for at least 24 hours. Symptoms include fever, coughing, sore throat and rhinitis.
                You can in any case go outside again on {{ @toDate($longestIsolationPeriodEndDate, '+1') }}.</li>
        @endif
    @else
        <li><strong>In all cases, stay at home up to and including {{ @toDate($contagiousPeriodEndDate) }} and do not receive visitors.</strong></li>
    @endif
    @if ($hasHouseMates)
        <li>Avoid contact with other members of your household. Stay in your own room and also sleep there by yourself. Do not let other members of your household enter this room.</li>
    @endif
    <li>Stay away from vulnerable people up to and including {{ @toDate($longestIsolationPeriodEndDate) }}.</li>
    @if (!$receivesExtensiveContactTracing)
        <li>Warn people you had contact with between {{ @toDate($contagiousPeriodStartDate) }} and the time you started self-isolating. Please refer these people to <a href="https://mijnvraagovercorona.nl/en">mijnvraagovercorona.nl/en</a>.</li>
    @endif
    @if ($isEduDaycare)
        <li>Tell the childcare centre, primary school or secondary school that you are infected.</li>
    @endif
    @if ($isHealthProfessional)
        <li>You work in healthcare. Discuss your return to work with your employer or company physician.</li>
    @endif
    <li>Regularly wash your hands with water and soap, and cough or sneeze into the crook of your elbow.</li>
</ul>
@if (!$hasSymptoms)
    <p><strong>You don’t have symptoms now. Do you start having symptoms at a later date?</strong></p>
    <ul>
        <li>Do not go outside until 5 days have passed since the start of your symptoms and you have not had any symptoms for at least 24 hours.
            Symptoms include fever, coughing, sore throat and rhinitis. If you still have symptoms after 5 days,
            stay at home until you have not had any symptoms for at least 24 hours, at most up to and including 10 days
            after the start of your symptoms.</li>
        <li>Stay away from vulnerable people until 10 days have passed since the start of your symptoms.</li>
    </ul>
@endif
