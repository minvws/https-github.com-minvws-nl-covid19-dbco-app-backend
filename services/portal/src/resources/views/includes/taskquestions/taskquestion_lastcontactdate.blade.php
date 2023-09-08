@error('lastcontactdate')
<div class="alert alert-danger">
    Laatste contact is een verplicht datumveld.
</div>
@enderror

<div class="row mt-3">
    <div class="col">
        <label for="date">
            <strong>Laatste contact</strong>
        </label>
        <input type="date"
               maxlength="255"
               class="form-control"
               id="lastcontactdate"
               name="lastcontactdate"
               value="{{ isset($task->dateOfLastExposure) ? $task->dateOfLastExposure->format('Y-m-d') : ''}}"
               placeholder="" />
    </div>
</div>
