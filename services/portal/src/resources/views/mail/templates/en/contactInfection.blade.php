<p>Dear {{ $name }},</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Kind regards,</p>

<p>Department for Communicable Diseases Control<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>This email contains confidential information. If you are not the addressee and have received this email by mistake, please let us know. We would also ask you to delete the email and not to view or share the content.</p>
