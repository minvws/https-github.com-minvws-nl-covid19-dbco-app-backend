<div class="row mt-3">
    <div class="col">
        <strong>{{ $question->label }} </strong>
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="firstName">Voornaam</label>
        <input type="text" maxlength="255" class="form-control" id="firstName" name="firstName" value="{{ $answers[$question->uuid]['firstname'] ?? '' }}" placeholder="">
    </div>
    <div class="col mt-1">
        <label for="lastName">Achternaam</label>
        <input type="text" maxlength="255" class="form-control" id="lastName" name="lastName" value="{{ $answers[$question->uuid]['lastname'] ?? '' }}" placeholder="">
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="phoneNumber">Telefoonnummer</label>
        <input type="text" maxlength="255" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ $answers[$question->uuid]['phonenumber'] ?? ''  }}" placeholder="">
    </div>
</div>
<div class="row">
    <div class="col mt-1">
        <label for="email">E-mailadres</label>
        <input type="text" maxlength="255" class="form-control" id="email" name="email" value="{{ $answers[$question->uuid]['email'] ?? ''  }}" placeholder="">
    </div>
</div>
