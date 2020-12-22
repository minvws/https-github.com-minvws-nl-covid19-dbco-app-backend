<div class="row">
    <div class="col">
        <h3> {{ $task['label'] }} </h3>
    </div>
</div>

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
