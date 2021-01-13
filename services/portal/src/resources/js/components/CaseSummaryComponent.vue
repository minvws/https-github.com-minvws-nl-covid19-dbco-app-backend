<template>
    <dbco-form-wrap title="Afronden & Status">
        <b-card class="w-100">
            <b-card-body>
                <h3>Contacten</h3>
                <contact-summary-component :covid-case="covidCase" />
            </b-card-body>
        </b-card>
    </dbco-form-wrap>
</template>

<script>
import DbcoFormWrap from "./DbcoFormWrap";
import ContactSummaryComponent from "./ContactSummaryComponent";
export default {
    name: "CaseSummaryComponent",
    components: {DbcoFormWrap,ContactSummaryComponent},
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
