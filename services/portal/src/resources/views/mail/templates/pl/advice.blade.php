<p>Do: {{ $name }}<br>
Szanowni Państwo,</p>

{{ $customTextPlaceholder }}
@if ($additionalAdvice)
    <p>
    {!! $additionalAdvice !!}
    </p>
@endif

<p>Więcej informacji na temat zasad postępowania w przypadku zakażenia koronawirusem można znaleźć na stronie: <a href="https://mijnvraagovercorona.nl/">https://mijnvraagovercorona.nl/</a>.</p>

<p>Życzymy Państwu szybkiego powrotu do zdrowia!</p>

<p>Z poważaniem,</p>

<p>Wydział ds. zwalczania chorób zakaźnych<br>
{{ $ggdRegion }}<br>
{{ $ggdPhoneNumber }}</p>

<p>Podczas kontaktu z nami, proszę mieć przy sobie Państwa numer sprawy: <strong>{{ $caseNumber }}</strong></p>

<p>Niniejsza wiadomość e-mail zawiera poufne informacje. Jeśli nie są Państwo jej adresatem i otrzymali ją przez przypadek, proszę nas o tym powiadomić. Proszę też usunąć tę wiadomość ze swojej skrzynki e-mail oraz nie czytać jej treści ani nie informować o niej innych osób.</p>
