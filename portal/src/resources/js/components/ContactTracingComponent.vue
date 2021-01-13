<template>
    <dbco-form-wrap title="Contactonderzoek">
    <!-- Start of table title component -->
        <b-card class="w-100">
            <b-card-body>
                <h3>Contacten toevoegen</h3>
                <contact-table-component :covid-case="value" />
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
    </dbco-form-wrap>
</template>

<script>
import DbcoFormWrap from "./DbcoFormWrap";
import ContactTableComponent from "./ContactTableComponent";
export default {
    name: "ContactTracingComponent",
    components: {DbcoFormWrap, ContactTableComponent},
    props: {
        value: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
        }
    }
}
</script>

<style scoped>

</style>
