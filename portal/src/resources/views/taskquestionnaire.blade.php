<div class="row">
    <div class="col">
        <h3> {{ $task['label'] }} </h3>
    </div>
</div>

<form method="post"
      action="{{ route('task-questionnaire-save', [$task['uuid']]) }}">
    @csrf

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
               value="{{ isset($task['dateOfLastExposure']) ? $task['dateOfLastExposure']->format('Y-m-d') : ''}}"
               placeholder="" />
    </div>
</div>

@foreach($questions as $question)
    @if (in_array($task['category'], $question->relevantForCategories))
        @include("taskquestion_".$question->questionType)
    @endif
@endforeach

<!-- Form submit -->
    <div class="btn-group mb-3 mt-3">
        <input type="submit" class="btn btn-primary" value="Opslaan" />
    </div>
    <!-- End of form submit-->

</form>
