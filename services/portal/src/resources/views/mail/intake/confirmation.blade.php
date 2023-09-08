{{ __('intake.confirmation.title', ['name' => $name]) }}
<p>
    {{ __('intake.confirmation.intro') }}
</p>

@if($advices)
    <h2>{{ __('intake.confirmation.advices.title') }}</h2>

    @foreach($advices as $advice)
        <h3>{{ $advice['title'] }}</h3>
        <p>
            {{ $advice['content'] }}<br>
            @isset($advice['link'])
                <a href="{{ $advice['link']['href'] }}">{{ $advice['link']['text'] }}</a>
            @endisset
        </p>
    @endforeach
@endif

@if($additionalAdvices)
    <h3>{{ __('intake.confirmation.additionalAdvices.title') }}</h3>

    <ul>
        @foreach($additionalAdvices as $additionalAdvice)
            <li>
                <a href="{{ $additionalAdvice['href'] }}">{{ $additionalAdvice['text'] }}</a>
            </li>
        @endforeach
    </ul>
@endif

<h3>{{ __('intake.confirmation.coronaMelderBanner.title') }}</h3>
<p>{{ __('intake.confirmation.coronaMelderBanner.text') }}</p>

<h3>{{ __('intake.confirmation.questions.title') }}</h3>
<p>{{ __('intake.confirmation.questions.text') }}</p>

<p>{{ __('intake.confirmation.outro.text') }}</p>

<p>{{ __('intake.confirmation.signature.text') }}<br/>
{{ __('intake.confirmation.signature.name') }}</p>

<p><small>{{ __('intake.confirmation.footer.text') }}</small></p>
