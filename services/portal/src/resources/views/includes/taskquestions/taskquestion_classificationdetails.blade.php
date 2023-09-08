<div class="row mt-3">
    <div class="col">
        <label for="category"><strong>Wat voor soort contact is dit?</strong></label>
        <select class="form-control" id="category" name="{{$question->uuid}}[value]">
            <option disabled selected>Selecteer</option>
            @php
                $options = [
                    '1' => '1 - Huisgenoot',
                    '2a' => '2a - Nauw contact',
                    '2b' => '2b - Nauw contact',
                    '3' => '3 - Overig contact'
                ];
            @endphp
            @foreach ($options as $value => $label)
                <option {{ (isset($answers[$question->uuid]) && $answers[$question->uuid] == $value) ? 'selected="selected"' : '' }} value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
