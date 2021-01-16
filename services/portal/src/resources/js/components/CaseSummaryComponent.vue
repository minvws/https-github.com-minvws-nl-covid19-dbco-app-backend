<template>
    <dbco-form-wrap title="Afronden & Status">
        <b-card class="w-100">
            <b-card-body>
                <h3>Contacten</h3>
                <contact-summary-component :covid-case="covidCase" />
            </b-card-body>
        </b-card>
        <div class="row bg-grey">
            <div class="col ml-1 mb-3 mt-4">
                <h2>Zet in HPZone</h2>
            </div>
        </div>
        <hpzone-export-component :case-uuid="covidCase.uuid" />
    </dbco-form-wrap>
</template>

<script>
import DbcoFormWrap from "./DbcoFormWrap";
import ContactSummaryComponent from "./ContactSummaryComponent";
import HpzoneExportComponent from "./HpzoneExportComponent";
export default {
    name: "CaseSummaryComponent",
    components: {DbcoFormWrap,ContactSummaryComponent,HpzoneExportComponent},
    props: {
        covidCase: {
            type: Object,
            required: true
        }
    }
}
</script>

<style scoped>

</style>
