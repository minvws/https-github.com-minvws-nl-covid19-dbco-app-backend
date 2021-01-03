<template>
    <div>
        <div v-if="cases.length" >
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
                    <tr v-for="(covidcase, $index) in cases" :key="$index" role="button" class="custom-link" @click="navigate(covidcase.editCommand)">
                        <th scope="row">{{ covidcase.name|truncate(30) }}</th>
                        <td>{{ covidcase.caseId|truncate(30) }}</td>
                        <td>{{ covidcase.dateOfSymptomOnset|dateFormatLong }}</td>
                        <td>
                            <span class="icon text-center">
                                <img :src="covidcase.statusIcon">
                            </span>
                            <span>{{ covidcase.statusLabel }}</span>
                        </td>
                        <td>{{ covidcase.updatedAt|dateFormatDeltaTime }}</td>
                        <td v-if="isPlanner" v-on:click.stop="">
                            <dbco-filterable-select @input="assign(covidcase.uuid, covidcase.assignedUuid)"
                                v-model="covidcase.assignedUuid"
                                :options="assignableUsers"
                                label-attribute="name"
                                value-attribute="uuid" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else-if="loaded" class="bg-white text-center pt-5 pb-5">
            Je hebt nog geen cases. Voeg deze toe door rechtsboven op de knop 'Nieuwe case' te drukken.
        </div>
        <div class="mb-3">
            <infinite-loading @infinite="infiniteHandler" spinner="spiral">
                <div slot="spinner"><span class="infinite-loader">Meer cases laden</span></div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </infinite-loading>
        </div>
    </div>
</template>

<script>

import DbcoFilterableSelect from "./DbcoFilterableSelect";
import InfiniteLoading from "vue-infinite-loading";

export default {
    name: "CovidCaseTableComponent",
    components: {
        DbcoFilterableSelect,
        InfiniteLoading
    },
    props: {
        isPlanner: Boolean,
        filter: String
    },
    data() {
        return {
            cases: [],
            page: 1,
            assignableUsers: [],
            loaded: false
        }
    },
    created() {
        if (this.isPlanner) {
            axios.get('./api/users/assignable').then(response => {
                this.assignableUsers = response.data.users
            })
        }
    },
    methods: {
        infiniteHandler($state) {
            axios.get('./api/cases/' + this.filter, {
                params: {
                    page: this.page,
                }
            }).then( response => {
                if (response.data.cases.data.length) {
                    this.page += 1
                    console.log(response.data.cases.data);
                    this.cases.push(...response.data.cases.data)
                    $state.loaded()
                } else {
                    $state.complete()
                }
                this.loaded = true
            })

        },
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
