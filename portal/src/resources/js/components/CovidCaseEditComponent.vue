<template>
    <div v-if="!this.caseUuid || loaded">
        <h1 class="ml-4 mt-3 mb-3"><span v-if="this.caseUuid || covidCase.name.length">{{ covidCase.name }}</span><span v-else>&lt;Nieuwe case&gt;</span></h1>
        <b-tabs class="">
            <b-tab title="Medische gegevens">
                <medical-data-component v-model="covidCase" @persist="persist()" />
            </b-tab>
            <b-tab title="Contactonderzoek" :disabled="covidCase.name.length == 0">
                contact-tracing-component v-model="covidCase" /
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
                uuid: null,
                name: '',
                caseId: '',
                dateOfSymptomOnset: '',
                dateOfTest: '',
                symptomatic: true,
                tasks: []
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
