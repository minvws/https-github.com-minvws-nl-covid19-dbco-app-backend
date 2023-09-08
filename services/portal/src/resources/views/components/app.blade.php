<div id="app" class="tw-flex tw-flex-col tw-min-h-screen">

    @if ($includeHeader ?? true)
        @include('includes/header')
    @endif

    {{ $slot }}


    @if ($includeFooter ?? true)
        @include('includes/footer')
    @endif

</div>
