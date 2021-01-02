<template>
    <div v-if="cases.length">
        <table class="table  table-rounded  table-hover  table-ggd">
            <colgroup>
                <col class="w-20">
                <col class="w-20">
                <col class="w-20">
                <col class="w-20">
                <col class="w-20">
                <col class="w-20" v-if="isPlanner">
            </colgroup>
            <thead>
            <tr>
                <th scope="col">Naam</th>
                <th scope="col">Casenr.</th>
                <th scope="col">Eerste ziektedag</th>
                <th scope="col">Status</th>
                <th scope="col">Laatst bewerkt</th>
                <th scope="col" v-if="isPlanner">Toegewezen aan</th>
            </tr>
            </thead>
            <tbody>
                <template v-for="covidcase in cases">
                    <tr role="button" class="custom-link" @click="navigate(covidcase.editCommand)">
                        <th scope="row">{{ covidcase.nameShort }}</th>
                        <td>{{ covidcase.caseIdShort }}</td>
                        <td>{{ covidcase.dateOfSymptomOnsetFormatted }}</td>
                        <td>
                            <span class="icon text-center">
                                <img :src="covidcase.statusIcon">
                            </span>
                            <span>{{ covidcase.statusLabel }}</span>
                        </td>
                        <td>{{ covidcase.updatedAtFormatted }}</td>
                        <td v-if="isPlanner" v-on:click.stop="">
                            <dbco-filterable-select @input="assign(covidcase.uuid, covidcase.assignedUuid)"
                                v-model="covidcase.assignedUuid"
                                :options="assignableUsers"
                                label-attribute="name"
                                value-attribute="uuid" />
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    <!-- End of table component -->
    [[ $cases->links() ]] Infinite scroll be here..
    </div>
    <div v-else-if="loaded" class="bg-white text-center pt-5 pb-5">
        Je hebt nog geen cases. Voeg deze toe door rechtsboven op de knop 'Nieuwe case' te drukken.
    </div>
</template>

<script>

import DbcoFilterableSelect from "./DbcoFilterableSelect";

export default {
    name: "CovidCaseTableComponent",
    components: {DbcoFilterableSelect},
    props: {
        isPlanner: Boolean,
        filter: String
    },
    data() {
        return {
            cases: [],
            assignableUsers: [],
            loaded: false
        }
    },
    created() {
        axios.get('./api/cases/' + this.filter).then(response => {
            this.cases = response.data.cases.data
            this.loaded = true
        })

        axios.get('./api/users/assignable').then(response => {
            this.assignableUsers = response.data.users
        })
    },
    methods: {
        navigate(url) {
            window.location = url
        },
        assign(caseUuid, newUserUuid) {
            axios.post('/api/assigncase', {
                'caseId': caseUuid,
                'userId': newUserUuid
            }).catch(function (error) {
                alert('Er ging iets fout bij het toewijzen van de case');
            });
        }
    }
}
</script>

<style scoped>

</style>
