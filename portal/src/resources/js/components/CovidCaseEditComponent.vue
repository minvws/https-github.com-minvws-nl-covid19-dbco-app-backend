<template>
    <div>
        <h3 class="mt-3 mb-3">{{ covidCase.name }}</h3>
        <b-tabs>
            <b-tab title="Medische gegevens" class="bg-light">
                <medical-data-component v-model="covidCase" @persist="persist()" />
            </b-tab>
            <b-tab title="Contactonderzoek" :disabled="covidCase.name.length == 0">
               <!-- <contact-tracing-component v-model="covidCase" /> -->
            </b-tab>
            <b-tab title="Afronden & status" v-model="covidCase" lazy>
             <!--   <case-summary-component/> -->
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
import MedicalDataComponent from "./MedicalDataComponent";
import CaseSummaryComponent from "./CaseSummaryComponent";
import ContactTracingComponent from "./ContactTracingComponent";
export default {
    name: "CovidCaseEditComponent",
    components: {
        ContactTracingComponent,
        CaseSummaryComponent,
        MedicalDataComponent
    },
    data() {
        return {
            covidCase: {
                "uuid": null,
                "name": '',
                "caseId": '',
                "dateOfSymptomOnset": '',
                "tasks": []
            },
            loaded: false
        }
    },
    props: {
        caseUuid: String
    },
    created() {
        if (this.caseUuid) {
            axios.get('/api/case/' + this.caseUuid).then(response => {
                this.covidCase = response.data.case
                this.loaded = true
            })
        }
    },
    methods: {
        persist() {
            const isNew = (this.covidCase.uuid == null);
            if (this.covidCase.name) {
                console.log('Persisting...', this.covidCase)
                axios.post('/api/case', {
                    case: this.covidCase
                }).then(response => {
                    // Only update the uuid after creation, to avoid needless propagations
                    console.log('this', this)
                    if (isNew) {
                        console.log('Pushing history')
                        this.covidCase.uuid = response.data.case.uuid
                        history.replaceState({}, '', '/editcase/' + response.data.case.uuid)
                    }
                }).catch(function (error) {
                    alert('Er ging iets fout bij het opslaan van de case')
                    console.log('Error!', error)
                })
            } else {
                console.log('Not saving until we have a name')
            }
        }
    }
}
</script>

<style scoped>

</style>
