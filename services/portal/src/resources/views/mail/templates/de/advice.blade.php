<p>Sehr geehrte/r {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Weitere Informationen zu den Verhaltensregeln für mit Covid-19 infizierte Personen finden Sie hier: <a href="https://mijnvraagovercorona.nl/">https://mijnvraagovercorona.nl/</a>.</p>

<p>Alles Gute und gute Besserung!</p>

<p>Mit freundlichen Grüßen,</p>

<p>Abteilung Bekämpfung von Infektionskrankheiten<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Halten Sie Ihr Aktenzeichen bereit, wenn Sie Kontakt zu uns aufnehmen: <strong>{{ $caseNumber }}</strong></p>

<p>Diese E-Mail enthält vertrauliche Informationen. Sind Sie nicht der bzw. die Adressat:in und haben Sie die E-Mail versehentlich erhalten? Dann teilen Sie uns das bitte mit. Ferner bitten wir Sie, die E-Mail zu löschen und den Inhalt weder zu lesen noch weiterzuleiten.</p>
