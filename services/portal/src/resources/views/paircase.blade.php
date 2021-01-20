<x-layout>
<x-slot name="title">
    Case detail
</x-slot>

<div class="container-xl questionform">

    @include ('navbar')
    <!-- End of navbar component -->
        <div class="row">
            <div class="col ml-5 mr-5">

    <!-- Start of table title component -->
    <div class="align-items-end  mb-3 mt-5">
        <h3 class="mb-0">@if ($includeQuestionNumber)<div class="question-nr">6</div>@endif Deel de code met de index</h3>
        <p class="mt-2 mb-0  ml-auto">Met deze code heeft de index toegang tot de contacten uit de aanleverlijst.</p>
    </div>
    <!-- End of table title component -->
    <div class="mt-4 mb-4 bg-white p-4 text-center" style="min-width: max-content; max-width: max-content; white-space: nowrap;">
        <h2>{{ $pairingCode }}</h2>
    </div>

    <div class="btn-group">
        <a href="/" class="btn btn-primary">Terug naar case overzicht</a>
    </div>
        </div>
</div>
</div>
<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</x-layout>
