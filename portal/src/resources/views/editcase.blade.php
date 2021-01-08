<x-layout>
<x-slot name="title">
    Case bewerken
</x-slot>

<div id="app" class="bg-white">
    @include ('navbar')
    <div class="container questionform">
        <covid-case-edit-component @if ($case ?? '') case-uuid="{{ $case->uuid }}" @endif></covid-case-edit-component>
    </div>
</div>

</x-layout>
