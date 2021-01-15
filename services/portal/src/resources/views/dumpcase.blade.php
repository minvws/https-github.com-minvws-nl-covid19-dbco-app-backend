<x-layout>
<x-slot name="title">
    Case export
</x-slot>


<div class="container-xl">

    @include ('navbar', [
        'returnPath' => route('case-view', [$case->uuid])
    ])

    <div class="row">
        <div class="col ml-5">

            <!-- Page title -->
            <h1 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                Overzetten naar HPZone
            </h1>
            <!-- End of page title -->


        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->

</x-layout>
