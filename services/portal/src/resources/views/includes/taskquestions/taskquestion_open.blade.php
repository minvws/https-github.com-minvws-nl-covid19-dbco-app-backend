<div class="row mt-3">
    <div class="col">
        <label for="date"><strong>{{ $question->label }} </strong></label>
        <textarea maxlength="255" class="form-control" id="open_{{$question->uuid}}" name="{{$question->uuid}}[value]" placeholder="">{{ $answers[$question->uuid]['value'] ?? ''}}</textarea>
    </div>
</div>
