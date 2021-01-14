<?php print_r($answers) ?>

<div class="row">
    <div class="col">
        <h3> {{ $task->label }} </h3>
    </div>
</div>

<form
    method="post"
    id="sidebar-task-edit"
    action="{{ route('task-questionnaire-save', [$task->uuid]) }}"
    data-taskuuid="{{ $task->uuid }}"
>
    @csrf

    @error('lastcontactdate')
    <div class="alert alert-danger">
        Laatste contact is verplicht datumveld.
    </div>
    @enderror

    <div class="row mt-3">
    <div class="col">
        <label for="date">
            <strong>Laatste contact</strong>
        </label>
        <input type="date"
               maxlength="255"
               class="form-control"
               id="lastcontactdate"
               name="lastcontactdate"
               value="{{ isset($task->dateOfLastExposure) ? $task->dateOfLastExposure->format('Y-m-d') : ''}}"
               placeholder="" />
    </div>
</div>

@foreach($questions as $question)
    @if (in_array($task->category, $question->relevantForCategories))
        @include("taskquestion_".$question->questionType)
    @endif
@endforeach

<!-- Form submit -->
    <div class="alert alert-warning mb-3 mt-3">
        Let op: als je wijzigingen opslaat, toont het portaal niet meer de nieuwe gegevens die de index in de app invult.
    </div>

    <div class="btn-group mb-3 mt-3">
        <input id="sidebar-submit" type="submit" class="btn btn-primary" value="Opslaan" onclick="javascript:submitTaskSidebar()"/>
    </div>
    <!-- End of form submit-->

</form>
