<template>
    <dbco-form-wrap title="Contactonderzoek">
    <!-- Start of table title component -->
        <b-card class="w-100">
            <b-card-body>
                <h3>Contacten toevoegen</h3>
            </b-card-body>
        </b-card>
        <b-card class="w-100 mt-3">
            <b-card-body>
                <h3>Index gaat zelf contacten aanvullen</h3>
            </b-card-body>
        </b-card>
        <b-card class="w-100 mt-3">
            <b-card-body>
                <h3>Contexten toevoegen</h3>
            </b-card-body>
        </b-card>

    <!-- Start of table component -->
    <table id="taskTable" class="table  table-rounded  table-bordered  table-has-header  table-has-footer  table-form  table-ggd" @if (old('addtasksnow') === 'nee') style="display:none" @endif>
    <!--
        Modify the col definitions in the colgroup below to change the widths of the the columns.
        The w-* classes will be automatically generated based on the $sizes array which is defined in the scss/_variables.scss
    -->
    <colgroup>
        <col class="w-25">
        <col class="w-25">
        <col class="w-8">
        <col class="w-15">
        <col class="w-15">
        <col class="w-5">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">Naam <i class="icon  icon--eye"></i></th>
        <th scope="col">Toelichting (optioneel) <i class="icon  icon--eye"></i></th>
        <th scope="col">Categorie</th>
        <th scope="col">Laatste contact</th>
        <th scope="col">Wie informeert</th>
        <th scope="col"></th>

    </tr>
    </thead>

    <tbody>
    <?php
                            $oldTasks = old('tasks');
                            if (is_array($oldTasks) && count($oldTasks)) {
                                $tasks = $oldTasks;
                            }
                            $row=0;
                        ?>
    @foreach ($tasks as $taskObj)
    <?php
                                    $task = (array)$taskObj;
                                    if (isset($task['dateOfLastExposure']) && is_object($task['dateOfLastExposure'])) {
                                        // template can't handle object because after validation 'old' is always an array, so we need
                                        // to be able to populate the field in both old and new cases, we use the stirng format.
                                        $task['dateOfLastExposure'] = $task['dateOfLastExposure']->format('Y-m-d');
    }
    ?>
    @include ('editcase_row')
    <?php $row++; ?>
    @endforeach
    </tbody>

    </table>
    <!-- End of table component -->

    <!-- Question: discuss app download and pairing with index -->
    <div class="align-items-end  mb-3 mt-5">
        <h3 class="mb-0"><div class="question-nr">{{ $questionNr++ }}</div> Gaat de index zelf gegevens aanvullen via de app?</h3>
        <p class="mt-2 mb-0  ml-auto">De index heeft de GGD Contact app nodig om op een veilige manier gegevens met de GGD te kunnen delen. Deze app is beschikbaar in de App Store en Google Play Store.</p>

        @if ($case->status == 'draft')
        @error('pairafteropen')
        <div class="alert alert-danger mt-3">
            Geef aan of de index een koppelcode voor de app nodig heeft.
        </div>
        @enderror

        <p>
        <div class="btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-outline-primary active">
                <input name="pairafteropen" type="radio" autocomplete="off" value="ja"
                       @if (old('pairafteropen') === 'ja') checked @endif
                /> Ja, maak koppelcode
            </label>
            <label class="btn btn-outline-primary active">
                <input name="pairafteropen" type="radio" autocomplete="off" value="nee"
                       @if (old('pairafteropen') === 'nee') checked @endif
                /> Nee
            </label>
        </div>
        </p>
        @endif
    </div>
    <!-- End of app and pairing question -->

    </dbco-form-wrap>
</template>

<script>
import DbcoFormWrap from "./DbcoFormWrap";
export default {
    name: "ContactTracingComponent",
    components: {DbcoFormWrap},
    props: {
        value: {
            type: Object,
            required: true
        }
    }
}
</script>

<style scoped>

</style>
