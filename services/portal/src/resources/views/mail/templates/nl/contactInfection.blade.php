<p>Beste {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Met vriendelijke groet,</p>

<p>Afdeling Infectieziektebestrijding<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Deze e-mail bevat vertrouwelijke informatie. Bent u niet de geadresseerde en ontvangt u de e-mail per ongeluk? Geef dit dan alstublieft aan ons door. Ook willen we u vragen de e-mail te verwijderen en de inhoud niet te bekijken of te delen.</p>
