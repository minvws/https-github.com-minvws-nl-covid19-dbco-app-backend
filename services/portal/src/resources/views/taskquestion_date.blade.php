<div class="row mt-3">
    <div class="col">
        <label for="date"><strong>{{ $question->label }} </strong></label>
        <input type="text" maxlength="255" class="form-control" id="date" name="{{$question->uuid}}[value]" value="{{ $answers[$question->uuid] ?? ''}}" placeholder="">
    </div>
</div>
