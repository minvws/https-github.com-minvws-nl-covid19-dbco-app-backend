<template>
    <div class="container-xl pt-4">
        <BRow>
            <BCol class="ml-5 mr-5">
                <Link v-if="!isDetailView || totalResults === 1" class="back-to-overview" href="/compliance">
                    &lt; Terug naar overzicht
                </Link>
                <Link v-else class="back-to-overview" @click="backToResults"> &lt; Terug naar zoekresultaten </Link>
            </BCol>
        </BRow>
        <BRow>
            <BCol class="ml-5 mr-5">
                <h4 class="mt-4 mb-2">Zoekopdracht</h4>
                <div class="card card--searchterms">
                    <BCardBody class="p-3">
                        <BRow>
                            <div
                                class="col-auto"
                                v-for="(searchValue, searchProp) in search"
                                v-show="searchValue"
                                :key="searchProp"
                            >
                                <b>{{ labels[searchProp] }}</b
                                ><br />
                                <span v-if="searchProp === 'dateOfBirth'">
                                    {{ $filters.dateFormat(searchValue) }}
                                </span>
                                <span v-else>
                                    {{ searchValue }}
                                </span>
                            </div>
                            <div class="col-auto align-self-center">
                                <Link @click="$bvModal.show('search')">Wijzigen</Link>
                            </div>
                        </BRow>
                    </BCardBody>
                </div>
            </BCol>
        </BRow>
        <div v-if="isLoading" class="row justify-content-center">
            <div class="col-auto">
                <i class="icon icon--m0 icon--spinner"></i>
            </div>
        </div>
        <div v-else class="row">
            <ComplianceSearchIndexCase v-if="indexCase" :uuid="indexCase" />
            <ComplianceSearchContactCase v-else-if="contactCase" :uuid="contactCase" />
            <div v-else class="col ml-5 mr-5">
                <h4 class="mt-4 mb-2">Dossiers</h4>
                <div class="card">
                    <BCardBody class="px-4 pt-0 pb-4">
                        <div
                            class="mt-4"
                            v-if="contactCases.length > 1 || (contactCases.length === 1 && indexCases.length > 0)"
                        >
                            <ComplianceContactCases
                                :contactCases="contactCases"
                                @navigate="selectCase($event, true, true)"
                            />
                        </div>
                        <div
                            class="mt-4"
                            v-if="indexCases.length > 1 || (indexCases.length === 1 && contactCases.length > 0)"
                        >
                            <ComplianceIndexCases
                                :indexCases="indexCases"
                                @navigate="selectCase($event, false, true)"
                            />
                        </div>

                        <div v-if="!contactCases.length && !indexCases.length" class="bg-white text-center pt-4">
                            Geen dossiers gevonden
                        </div>
                    </BCardBody>
                </div>
            </div>
        </div>
        <BModal title="Zoekopdracht wijzigen" id="search" size="xl" hide-footer centered scrollable>
            <BRow>
                <BCol class="p-0">
                    <ComplianceSearch :values="$as.any(search)" @submit="$bvModal.hide('search')" />
                </BCol>
            </BRow>
        </BModal>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { caseApi } from '@dbco/portal-api';
import ComplianceContactCases from '@/components/compliance/ComplianceContactCases/ComplianceContactCases.vue';
import ComplianceIndexCases from '@/components/compliance/ComplianceIndexCases/ComplianceIndexCases.vue';
import ComplianceSearch from '@/components/compliance/ComplianceSearch/ComplianceSearch.vue';
import ComplianceSearchContactCase from '@/components/compliance/ComplianceSearchContactCase/ComplianceSearchContactCase.vue';
import ComplianceSearchIndexCase from '@/components/compliance/ComplianceSearchIndexCase/ComplianceSearchIndexCase.vue';
import type { SearchQueryDTO, TaskSearchResultDTO, IndexSearchResultDTO } from '@dbco/portal-api/case.dto';
import { Link } from '@dbco/ui-library';

interface Data {
    isLoading: boolean;
    contactCases: TaskSearchResultDTO[];
    contactCase: string | null;
    indexCases: IndexSearchResultDTO[];
    indexCase: string | null;
    search: SearchQueryDTO;
    labels: { [key: string]: string };
}

export default defineComponent({
    name: 'ComplianceSearchResults',
    components: {
        ComplianceContactCases,
        ComplianceIndexCases,
        ComplianceSearch,
        ComplianceSearchIndexCase,
        ComplianceSearchContactCase,
        Link,
    },
    data() {
        return {
            isLoading: true,
            contactCases: [],
            contactCase: null,
            indexCases: [],
            indexCase: null,
            search: {
                lastname: null,
                email: null,
                dateOfBirth: null,
                phone: null,
                identifier: null,
                caseUuid: null,
            },
            labels: {
                lastname: 'Achternaam',
                email: 'E-mailadres',
                dateOfBirth: 'Geboortedatum',
                phone: 'Telefoonnummer',
                identifier: 'HPZone-, BCO Portaal- of monsternummer',
                caseUuid: 'Case UUID',
            },
        } as Data;
    },
    created() {
        window.addEventListener('hashchange', this.loadSearchFromHash);

        // Initial search request
        void this.loadSearchFromHash();
    },
    destroyed() {
        window.removeEventListener('hashchange', this.loadSearchFromHash);
    },
    computed: {
        isDetailView() {
            return Boolean(this.indexCase || this.contactCase);
        },
        totalResults() {
            return this.indexCases.length + this.contactCases.length;
        },
    },
    methods: {
        // Catch browser history navigation
        loadCaseFromHash() {
            const params = new URLSearchParams(window.location.hash.slice(1));
            const indexUuid = params.get('indexUuid');
            if (indexUuid && this.indexCases.some((c) => c.uuid === indexUuid)) {
                this.indexCase = indexUuid;
                this.contactCase = null;

                return true;
            } else {
                this.indexCase = null;
            }

            const contactUuid = params.get('contactUuid');
            if (contactUuid && this.contactCases.some((c) => c.uuid === contactUuid)) {
                this.contactCase = contactUuid;
                this.indexCase = null;

                return true;
            } else {
                this.contactCase = null;
            }

            return false;
        },
        async loadSearchFromHash() {
            this.$bvModal.hide('search'); // close search modal on navigation

            const params = new URLSearchParams(window.location.hash.slice(1));
            const searchFromHash: { [key: string]: string | null } = {};
            Object.keys(this.search).forEach((key) => {
                searchFromHash[key] = params.get(key);
            });

            if (JSON.stringify(this.search) === JSON.stringify(searchFromHash)) {
                // Search was not updated
                this.isLoading = false;

                // Trigger loading case from updated hash
                this.loadCaseFromHash();

                return;
            }

            this.search = searchFromHash;
            this.isLoading = true;
            // don't post any null or empty values
            const payload = Object.fromEntries(Object.entries(this.search).filter(([_, v]) => v));

            try {
                const data = await caseApi.search(payload);
                this.indexCases = (data && data?.cases) || [];
                this.contactCases = (data && data?.contacts) || [];
            } catch (error) {
                // For now, in case of backend validation errors, display the no results page
                this.isLoading = false;
            }

            // Trigger loading case from updated hash
            if (!this.loadCaseFromHash()) {
                // No selection in the hash
                if (this.indexCases.length === 1 && this.contactCases.length === 0) {
                    // Select the only index case result
                    await this.selectCase(this.indexCases[0].uuid, false, false);
                }
                if (this.contactCases.length === 1 && this.indexCases.length === 0) {
                    // Select the only contact case result
                    await this.selectCase(this.contactCases[0].uuid, true, false);
                }
            }

            this.isLoading = false;
        },
        selectCase(uuid: string, isContact: boolean, navigate: boolean) {
            const urlParam = isContact ? 'contactUuid' : 'indexUuid';

            // add to url params
            const params = new URLSearchParams(window.location.hash.slice(1));
            params.set(urlParam, uuid);
            if (navigate) {
                // navigate to the detail page
                window.location.hash = params.toString();
            } else {
                // don't simulate navigation but replace current history
                window.history.replaceState({ uuid }, '', `${window.location.pathname}#${params.toString()}`);
                if (isContact) {
                    this.contactCase = uuid;
                } else {
                    this.indexCase = uuid;
                }
            }
        },
        backToResults() {
            const params = new URLSearchParams();
            Object.entries(this.search).forEach(([key, value]) => {
                if (!value) return;

                params.set(key, value.toString());
            });

            this.indexCase = null;
            this.contactCase = null;
            window.location.hash = params.toString();
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/_variables.scss';

.back-to-overview {
    color: $black;
    font-weight: 500;
}

.card--searchterms {
    width: max-content;
}
</style>
