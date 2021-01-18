<div class="row">
    <div class="col">
        <h3> {{ $task->label }} </h3>
    </div>
</div>

<form method="post"
      id="sidebar-task-edit"
      action="">
@csrf

@foreach($questions as $question)
    @if (in_array($task->category, $question->relevantForCategories))
        @if (isset($answers[$question->uuid]) && $answers[$question->uuid] === \App\Models\IndecipherableAnswer::INDECIPHERABLE)
            @include("taskquestion_indecipherable")
        @else
            @include("taskquestion_" . $question->questionType)
        @endif
    @endif
@endforeach

<!-- Form submit -->
    <div class="alert alert-warning mb-3 mt-3">
        Let op: als je wijzigingen opslaat, toont het portaal niet meer de nieuwe gegevens die de index in de app invult.
    </div>

    <div class="btn-group mb-3 mt-3">
        <button id="sidebar-task-submit" data-task-uuid="{{ $task->uuid }}" type="button" class="btn btn-primary">Opslaan</button>
    </div>
    <!-- End of form submit-->
</form>
