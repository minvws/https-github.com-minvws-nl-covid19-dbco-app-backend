<template>
    <div>
        <div v-if="cases.length" class="mt-3">
            <table class="table table-rounded table-hover table--clickable table-ggd table--align-start">
                <colgroup>
                    <col class="w-15" />
                    <col class="w-20" />
                    <col class="w-15" />
                    <col class="w-25" />
                    <col class="w-15" />
                    <col class="w-10" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">Casenr.</th>
                        <th scope="col">Naam</th>
                        <th scope="col">Eerste ziektedag</th>
                        <th scope="col">Label</th>
                        <th scope="col">Status</th>
                        <th scope="col">Laatst bewerkt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(covidCase, $index) in cases"
                        :key="$index"
                        :class="['custom-link']"
                        @click="navigate(covidCase.editCommand)"
                        data-testid="case-table-row"
                    >
                        <th scope="row">
                            {{
                                covidCase.organisation && !covidCase.organisation.isCurrent
                                    ? `${covidCase.organisation.abbreviation}-${covidCase.caseId}`
                                    : covidCase.caseId
                            }}
                        </th>
                        <td>
                            <Link :href="covidCase.editCommand">
                                {{ $filters.truncate(covidCase.name, 30) }}
                            </Link>
                        </td>
                        <td>{{ $filters.dateFormatLong(covidCase.dateOfSymptomOnset) }}</td>
                        <td>
                            {{ covidCase.caseLabels.map((caseLabel) => caseLabel.label).join(', ') }}
                        </td>
                        <td>
                            <span class="icon text-center">
                                <img
                                    :alt="statusLabel(covidCase.bcoStatus, covidCase.indexStatus)"
                                    :src="statusIcon(covidCase.bcoStatus, covidCase.indexStatus)"
                                />
                            </span>
                            <span>{{ statusLabel(covidCase.bcoStatus, covidCase.indexStatus) }}</span>
                        </td>
                        <td>{{ $filters.dateFormatDeltaTime(covidCase.updatedAt) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else-if="loaded" class="bg-white text-center pt-5 pb-5">Je hebt nog geen cases.</div>
        <div class="mb-3">
            <InfiniteLoading
                ref="infiniteLoading"
                :identifier="infiniteId"
                @infinite="infiniteHandler"
                spinner="spiral"
            >
                <div slot="spinner">
                    <Spinner />
                    <span class="infinite-loader">Meer cases laden</span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script>
import { caseApi } from '@dbco/portal-api';
import InfiniteLoading from 'vue-infinite-loading';
import { BcoStatusV1, bcoStatusV1Options, IndexStatusV1, indexStatusV1Options } from '@dbco/enum';
import { Link, Spinner } from '@dbco/ui-library';
import { statusSvgs } from './statusSvgs';

export default {
    name: 'CovidCaseUserTable',
    components: {
        InfiniteLoading,
        Spinner,
        Link,
    },
    data() {
        return {
            cases: [],
            loaded: false,
            bcoStatus: bcoStatusV1Options,
            indexStatus: indexStatusV1Options,
            infiniteId: Date.now(),
            page: 1,
        };
    },
    methods: {
        async infiniteHandler() {
            const response = await caseApi.getCases(this.page);
            if (response.cases.data.length) {
                this.page += 1;
                this.cases.push(...response.cases.data);
                //  setTimeout(function () { // enable this to show the spinner for longer to debug the spinner
                this.$refs.infiniteLoading.stateChanger.loaded();
                // }, 2000);
            } else {
                this.$refs.infiniteLoading.stateChanger.complete();
            }
            this.loaded = true;
        },
        navigate(url) {
            window.location = url;
        },
        resetTable() {
            this.page = 1;
            this.cases = [];

            // Reset vue-infinite-loading component
            this.infiniteId = Date.now();
        },
        statusIcon(bcoStatus, indexStatus) {
            let icon = null;
            if (
                bcoStatus === BcoStatusV1.VALUE_open &&
                ![IndexStatusV1.VALUE_initial, IndexStatusV1.VALUE_pairing_request_accepted].includes(indexStatus)
            ) {
                icon = indexStatus;
            } else {
                icon = bcoStatus;
            }

            return statusSvgs[icon];
        },
        statusLabel(bcoStatus, indexStatus) {
            if (bcoStatus === BcoStatusV1.VALUE_open) {
                return this.indexStatus[indexStatus];
            } else {
                return this.bcoStatus[bcoStatus];
            }
        },
    },
};
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

tr td a {
    color: inherit;
}
</style>
