<template>
    <div>
        <h3 class="mt-3 mb-3">{{ covidCase.name }}</h3>
        <b-tabs>
            <b-tab title="Medische gegevens" class="bg-light">
                <medical-data-component v-model="covidCase" />
            </b-tab>
            <b-tab title="Contactonderzoek">
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
                "uuid": '',
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
}
</script>

<style scoped>

</style>
