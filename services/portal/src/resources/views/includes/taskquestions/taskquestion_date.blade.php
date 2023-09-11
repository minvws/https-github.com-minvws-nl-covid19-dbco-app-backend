<div class="row mt-3">
    <div class="col">
        @error($question->uuid . '.value')
        <div class="alert alert-danger">
            Vul een geldige datum.
        </div>
        @enderror

        <label for="date"><strong>{{ $question->label }} </strong></label>
        <input type="date" maxlength="255" class="form-control" id="date" name="{{$question->uuid}}[value]" value="{{ $answers[$question->uuid]['value'] ?? ''}}" placeholder="">
    </div>
</div>
