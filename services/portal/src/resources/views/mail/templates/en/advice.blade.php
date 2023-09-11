<p>Dear {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>For more information about the rules if you have been infected with the coronavirus, please visit: <a href="https://mijnvraagovercorona.nl/">https://mijnvraagovercorona.nl/</a>.</p>

<p>We hope you feel better soon.</p>

<p>Kind regards,</p>

<p>Department for Communicable Diseases Control<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Please mention your reference number when contacting us: <strong>{{ $caseNumber }}</strong></p>

<p>This email contains confidential information. If you are not the addressee and have received this email by mistake, please let us know. We would also ask you to delete the email and not to view or share the content.</p>
