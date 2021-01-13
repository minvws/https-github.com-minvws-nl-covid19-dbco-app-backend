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
                        <b-form-input @change="persist(task)" maxlength="255" placeholder="Voeg contact toe" v-model="task.label" :state="isValid(task.uuid, 'label')" />
                    </span>
                </b-td>
                <b-td>
                    <b-form-input @change="persist(task)" maxlength="255" placeholder="Bijv. collega of trainer" v-model="task.taskContext" :state="isValid(task.uuid, 'taskContext')" />
                </b-td>
                <b-td>
                    <dbco-category-select @input="persist(task)" v-model="task.category" :state="isValid(task.uuid,  'category')" />
                </b-td>
                <b-td>
                    <dbco-datepicker
                        @select="persist(task)"
                        v-model="task.dateOfLastExposure"
                        :symptomatic="covidCase.symptomatic"
                        :symptom-date="covidCase.dateOfSymptomOnset"
                        :id="'date_' + (task.uuid ? task.uuid : 'ph')"
                        variant="dropdown"
                        :state="isValid(task.uuid, 'dateOfLastExposure')"
                        display-source-period />
                </b-td>
                <b-td>
                    <b-form-radio-group
                        @change="persist(task)"
                        v-model="task.communication"
                        :options="communicationOptions"
                        button-variant="outline-primary"
                        name="radio-btn-symptoms"
                        size="sm"
                        buttons
                    />
                </b-td>
                <b-td class="text-center">
                    <b-button variant="link" @click="deleteTask(task)" v-if="task.uuid"><i class="icon  icon--delete  icon--m0"></i></b-button>
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
        covidCase: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            tasks: [],
            loaded: false,
            validationErrors: [],
            communicationOptions: [
                { 'text': 'GGD', 'value': 'staff' },
                { 'text': 'Index', 'value': 'index' }
            ]
        }
    },
    mounted() {
        if (this.covidCase) {
            axios.get('/api/cases/' + this.covidCase.uuid +'/tasks').then(response => {
                this.tasks = response.data.tasks

                this.tasks.push({}) // Always add a placeholder for new records

                this.loaded = true
            })
        }
    },
    methods: {
        persist(task) {
            console.log("persisting.. ", task)

            if (task.uuid) {
                // Update
                axios.post('/api/tasks/' + task.uuid, {
                    task
                }).then(response => {
                    this.validationErrors[task.uuid] = []
                }).catch(error => {
                    console.log('error', error)
                    if (error.response.status == 422) {
                        // Todo/fixme: this validation doesn't work yet, it renders the
                        // status too late (upon re-render) (we should refresh/touch something to
                        // immediately render the errors)
                        this.validationErrors[task.uuid] = Object.keys(error.response.data.errors)
                    } else {
                        alert('Er ging iets mis bij het opslaan van de nieuwe contactpersoon')
                        console.log('Error!', error)
                    }
                })
            } else {
                // Create new
                axios.post('/api/cases/' + this.covidCase.uuid + '/tasks', {
                    task
                }).then(response => {
                    this.validationErrors[task.uuid] = [] // clear errors on the undefined row
                    task.uuid = response.data.task.uuid
                    // Add new placeholder
                    this.tasks.push({dateOfSymptomOnset: '2021-01-01T10:00:00.000Z'})
                }).catch( error => {
                    if (error.response && error.response.status == 422) {
                        this.validationErrors[task.uuid] = Object.keys(error.response.data.errors)
                    } else {
                        alert('Er ging iets mis bij het opslaan van de nieuwe contactpersoon')
                        console.log('Error!', error)
                    }
                })
            }
        },
        deleteTask(task) {
            console.log("Deleting.. ", task)
            axios.delete('/api/tasks/' + task.uuid).then(response => {
                this.tasks = this.tasks.filter(taskItem => taskItem.uuid !== task.uuid)
            })
        },
        isValid(taskUuid, fieldName) {
            // Todo fixme, doesn't yet fully work, see todo earlier above
            if (this.validationErrors[taskUuid] && this.validationErrors[taskUuid].includes('task.' + fieldName)) {
                return false
            }
            return null // don't show green 'valid' indicator, but simply nothing
        }
    }
}
</script>

<style scoped>

</style>
