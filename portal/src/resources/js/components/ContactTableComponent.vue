<template>
    <b-table-simple class="table-form table-ggd">
        <colgroup>
            <col class="w-20">
            <col class="w-40">
            <col class="w-10">
            <col class="w-15">
            <col class="w-10">
            <col class="w-5">
        </colgroup>
        <b-thead>
            <b-tr>
                <b-th scope="col">Naam <i class="icon  icon--eye"></i></b-th>
                <b-th scope="col">Toelichting (optioneel) <i class="icon  icon--eye"></i></b-th>
                <b-th scope="col">Categorie</b-th>
                <b-th scope="col">Laatste contact</b-th>
                <b-th scope="col">Wie informeert</b-th>
                <b-th scope="col"></b-th>
            </b-tr>
        </b-thead>
        <b-tbody>
            <b-tr v-for="(task, $index) in tasks" :key="$index">
                <b-td>
                    <span v-if="task.derivedLabel">
                        {{ task.derivedLabel }}
                    </span>
                    <span v-else>
                        <b-form-input maxlength="255" placeholder="Voeg contact toe" v-model="task.label" />
                    </span>
                </b-td>
                <b-td>
                    <b-form-input maxlength="255" placeholder="Bijv. collega of trainer" v-model="task.taskContext" />
                </b-td>
                <b-td>
                    <dbco-category-select v-model="task.category" />
                </b-td>
                <b-td>
                    <dbco-datepicker v-model="task.dateOfLastExposure" :id="'date_' + (task.uuid ? task.uuid : 'ph')" variant="dropdown" />
                </b-td>
                <b-td>
                    <b-form-radio-group
                        v-model="task.communication"
                        :options="communicationOptions"
                        button-variant="outline-primary"
                        name="radio-btn-symptoms"
                        size="sm"
                        buttons
                    />
                </b-td>
                <b-td class="text-center">
                    <b-button variant="link" @click="" v-if="task.uuid"><i class="icon  icon--delete  icon--m0"></i></b-button>
                </b-td>
            </b-tr>

        </b-tbody>

    </b-table-simple>
</template>

<script>
import DbcoCategorySelect from "./DbcoCategorySelect";
import DbcoDatepicker from "./DbcoDatepicker";
export default {
    name: "ContactTableComponent",
    components: {DbcoDatepicker, DbcoCategorySelect},
    props: {
        caseUuid: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            tasks: [],
            loaded: false,
            communicationOptions: [
                { 'text': 'GGD', 'value': 'staff' },
                { 'text': 'Index', 'value': 'index' }
            ]
        }
    },
    mounted() {
        if (this.caseUuid) {
            axios.get('/api/cases/' + this.caseUuid +'/tasks').then(response => {
                this.tasks = response.data.tasks

                this.tasks.push({}) // Always add a placeholder for new records

                this.loaded = true
            })
        }
    },
}
</script>

<style scoped>

</style>
