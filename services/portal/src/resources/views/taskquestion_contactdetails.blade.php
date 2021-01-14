<div class="row mt-3">
    <div class="col">
        <strong>{{ $question->label }}</strong>
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="firstname">Voornaam</label>
        <input type="text" maxlength="255" class="form-control" id="firstname" name="{{$question->uuid}}[{{\App\Models\ContactDetailsAnswer::FIELD_FIRSTNAME}}]" value="{{ $answers[$question->uuid][\App\Models\ContactDetailsAnswer::FIELD_FIRSTNAME] ?? '' }}" placeholder="">
    </div>
    <div class="col mt-1">
        <label for="lastname">Achternaam</label>
        <input type="text" maxlength="255" class="form-control" id="lastname" name="{{$question->uuid}}[{{\App\Models\ContactDetailsAnswer::FIELD_LASTNAME}}]" value="{{ $answers[$question->uuid][\App\Models\ContactDetailsAnswer::FIELD_LASTNAME] ?? '' }}" placeholder="">
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="phonenumber">Telefoonnummer</label>
        <input type="text" maxlength="255" class="form-control" id="phonenumber" name="{{$question->uuid}}[{{\App\Models\ContactDetailsAnswer::FIELD_PHONENUMBER}}]" value="{{ $answers[$question->uuid][\App\Models\ContactDetailsAnswer::FIELD_PHONENUMBER] ?? ''  }}" placeholder="">
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="email">E-mailadres</label>
        <input type="text" maxlength="255" class="form-control" id="email" name="{{$question->uuid}}[{{\App\Models\ContactDetailsAnswer::FIELD_EMAIL}}]" value="{{ $answers[$question->uuid][\App\Models\ContactDetailsAnswer::FIELD_EMAIL] ?? ''  }}" placeholder="">
    </div>
</div>
