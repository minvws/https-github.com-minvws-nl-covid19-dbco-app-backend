<tr>
    <td>
        <input class="form-control" type="hidden" name="tasks[{{ $row }}][uuid]" value="{{ $task['uuid'] ?? '' }}">
        <label class="sr-only" for="label">Label</label>
        <input type="text" maxlength="255" class="form-control auto-row-clone" id="label" name="tasks[{{ $row }}][label]" value="{{ $task['label'] ?? '' }}" placeholder="Voeg contact toe">
    </td>
    <td>
        <label class="sr-only" for="context1">Context</label>
        <input type="text" maxlength="255" class="form-control" id="context1" name="tasks[{{ $row }}][taskContext]" value="{{ $task['taskContext'] ?? '' }}" placeholder="Bijv. collega of trainer">
    </td>
    <td>
        <label class='sr-only' for="categorie1">Categorie</label>
        <select class="form-control" id="category1" name="tasks[{{ $row }}][category]">
            <option disabled selected>Selecteer</option>
            <?php $options = array('1', '2a', '2b', '3'); ?>
            @foreach ($options as $option)
                <option {{ (isset($task['category']) && $option == $task['category']) ? 'selected="selected"' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <label class="sr-only" for="date1">Laatste contact</label>
        <select class="form-control" id="lastcontact1" name="tasks[{{ $row }}][dateOfLastExposure]">
            @for ($i = 13; $i >= 0; $i--)
                <?php
                $date = Date::parse("-$i days")->format("Y-m-d");
                $label = Date::parse("-$i days")->format('j M l');
                $selected = (!isset($task['dateOfLastExposure']) || $date != $task['dateOfLastExposure'] ?: 'selected="selected"')
                ?>
                <option value="{{ $date }}" {{ $selected }}>{{ $label }}</option>
            @endfor
            <option disabled selected>Selecteer</option>
        </select>
    </td>
    <td>
        <label class='sr-only' for="informeren1">Wie informeert</label>
        <select class="form-control" id="informeren1" name="tasks[{{ $row }}][communication]">
            <option disabled selected>Selecteer</option>
            <option value="staff" {{ (!isset($task['communication']) || $task['communication'] != 'staff') ?: 'selected="selected"' }}>GGD</option>
            <option value="index" {{ (!isset($task['communication']) || $task['communication'] != 'index') ?: 'selected="selected"' }}>Index</option>
        </select>
    </td>
    <td class="text-center">
        <button class="btn btn-delete @if(!isset($task['label'])) invisible @endif"><i class="icon  icon--delete  icon--m0"></i></button>
    </td>
</tr>
