<?php

declare(strict_types=1);

use App\Models\Task;

/**
 * @var Task $task
 */
?>
<div class="row">
    <div class="col">
        <h3> {{ $task->label }} </h3>
    </div>
</div>

@foreach($questions as $question)
    @if ($question->questionType === 'classificationdetails')
        @continue
    @endif

    @if (in_array($task->category, $question->relevantForCategories))
        @if (isset($answers[$question->uuid]) && $answers[$question->uuid] === \App\Models\IndecipherableAnswer::INDECIPHERABLE)
            @include("includes/taskquestions/taskquestion_indecipherable")
        @else
            @include("includes/taskquestions/taskquestion_" . $question->questionType)
        @endif
    @endif
@endforeach

<div class="row mt-3">
    <div class="col">
        <h3>GGD</h3>
    </div>
</div>

<div class="row">
    <div class="col mt-1">
        <label for="dossier_number">Dossier Nummer</label>
        <input type="text" maxlength="255" class="form-control" id="dossier_number" name="ggd_dossier_number"
               value="{{ $task->dossierNumber ?? ''  }}" placeholder="">
    </div>
</div>
