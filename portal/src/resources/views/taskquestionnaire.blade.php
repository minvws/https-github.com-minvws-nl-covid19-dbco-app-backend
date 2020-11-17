<div class="row">
    <div class="col">
        <h3> {{ $task['label'] }} </h3>
    </div>
</div>

@foreach($questions as $question)
    @if (in_array($task['category'], $question->relevantForCategories))
        @include("taskquestion_".$question->questionType)
    @endif
@endforeach
