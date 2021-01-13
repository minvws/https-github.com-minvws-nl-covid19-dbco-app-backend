<template>
    <b-table-simple class="table-form table-ggd w-75">
        <colgroup>
            <col class="w-40">
            <col class="w-30">
            <col class="w-15">
            <col class="w-15">
        </colgroup>
        <b-thead>
            <b-tr>
                <b-th scope="col">Naam en toelichting</b-th>
                <b-th scope="col">Laatste contact</b-th>
                <b-th scope="col" class="text-center">Geïnformeerd</b-th>
                <b-th scope="col" class="text-center">Gegevens</b-th>
            </b-tr>
        </b-thead>
        <b-tbody>
            <b-tr v-for="(task, $index) in tasks" :key="$index">
                <b-td>
                    <span v-if="task.derivedLabel">
                        <strong>{{ task.derivedLabel }}</strong>
                    </span>
                    <span v-else>
                        <strong>{{ task.label }}</strong>
                    </span>
                    <br/>{{ task.taskContext }}
                </b-td>
                <b-td>
                    <strong>{{ task.dateOfLastExposure | dateFormatLong }}</strong>
                    <br/>{{ task.category | categoryFormatFull }}
                </b-td>
                <b-td class="text-center">
                    <img v-if="task.informedByIndex" src="/images/done.svg">
                    <span v-else>Nog niet</span>
                </b-td>
                <b-td class="text-center pt-3 pb-3">
                    <img v-if="task.progress=='complete'" src="/images/check-100.svg">
                    <img v-else-if="task.progress=='contactable'" src="/images/check-50.svg">
                    <img v-else src="/images/check-warn.svg">
                </b-td>
            </b-tr>

        </b-tbody>

    </b-table-simple>
</template>

<script>
import DbcoCategorySelect from "./DbcoCategorySelect";
import DbcoDatepicker from "./DbcoDatepicker";
export default {
    name: "ContactSummaryComponent",
    props: {
        covidCase: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            tasks: [],
            loaded: false,
        }
    },
    mounted() {
        if (this.covidCase && this.covidCase.uuid) {
            this.loadContacts(this.covidCase.uuid)
        } else {
            // waiting for case
        }
    },
    watch: {
        'covidCase.uuid': function (newVal, oldVal) {
            // Case has changed, reload the contacts that belong to that case.
            if (newVal != oldVal) {
                this.loadContacts(newVal)
            }
        }
    },
    methods: {
        loadContacts(caseUuid) {
            axios.get('/api/cases/' + caseUuid +'/tasks?includeProgress=true').then(response => {
                this.tasks = response.data.tasks
                this.loaded = true
            })
        }
    }
}
</script>

<style scoped>

</style>
