<p>Beste {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Meer informatie over de leefregels als u corona heeft, leest u op: <a href="https://mijnvraagovercorona.nl/">https://mijnvraagovercorona.nl/</a>.</p>

<p>We wensen u sterkte en beterschap toe.</p>

<p>Met vriendelijke groet,</p>

<p>Afdeling Infectieziektebestrijding<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Houd uw dossiernummer bij de hand als u contact met ons opneemt: <strong>{{ $caseNumber }}</strong></p>

<p>Deze e-mail bevat vertrouwelijke informatie. Bent u niet de geadresseerde en ontvangt u de e-mail per ongeluk? Geef dit dan alstublieft aan ons door. Ook willen we u vragen de e-mail te verwijderen en de inhoud niet te bekijken of te delen.</p>
