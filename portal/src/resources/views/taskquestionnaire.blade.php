<div class="row">
    <div class="col">
        <h3> {{ $task['label'] }} </h3>
    </div>
</div>

<form method="post"
      action="{{ route('task-questionnaire-save', [$task['uuid']]) }}">
@csrf

    <!-- Questionnaire -->
    @foreach($questions as $question)
        @if (in_array($task['category'], $question->relevantForCategories))
            @include("taskquestion_".$question->questionType)
        @endif
    @endforeach
    <!-- End of questionnaire -->

    <!-- Form submit -->
    <div class="btn-group mb-3 mt-3">
        <input type="submit" class="btn btn-primary" value="Opslaan" />
    </div>
    <!-- End of form submit-->

</form>
