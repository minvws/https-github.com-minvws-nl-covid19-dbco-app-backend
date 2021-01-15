<div class="row mt-3">
    <div class="col">
        <label for="iets"><strong>{{ $question->label }} </strong></label>
        <p>
            {!! $question->description !!}
        </p>
        <select class="form-control" id="" name="{{$question->uuid}}['value']">
            <option disabled selected>Selecteer</option>
            @foreach ($question->answerOptions as $answerOption)
                <option {{ isset($answers[$question->uuid]) && $answers[$question->uuid] == $answerOption->value ? 'selected="selected"' : '' }} value="{{ $answerOption->value }}">{{ $answerOption->label }}</option>
            @endforeach
        </select>
    </div>
</div>

