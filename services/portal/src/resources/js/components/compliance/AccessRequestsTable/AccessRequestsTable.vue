<template>
    <div>
        <div v-if="isLoading" class="row justify-content-center">
            <div class="col-auto">
                <i class="icon icon--m0 icon--spinner"></i>
            </div>
        </div>
        <div v-else-if="accessRequests.length" class="mt-5">
            <h4 v-if="accessRequests.length === 1">1 resultaat</h4>
            <h4 v-else>{{ accessRequests.length }} resultaten</h4>
            <table class="table table-rounded table-hover table-ggd">
                <colgroup>
                    <col class="w-20" />
                    <col class="w-30" />
                    <col class="w-20" />
                    <col class="w-30" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">Datum</th>
                        <th scope="col">Naam</th>
                        <th scope="col">Gebruiker</th>
                        <th scope="col">Uitgevoerde actie</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(accessRequest, $index) in visibleAccessRequests" :key="$index">
                        <td>{{ $filters.dateFormat(accessRequest.date) }}</td>
                        <td>{{ $filters.truncate(accessRequest.name, 30) }}</td>
                        <td>{{ $filters.truncate(accessRequest.user, 30) }}</td>
                        <td>
                            <div class="d-flex justify-content-between">
                                <div data-testid="actions">{{ actions(accessRequest) }}</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mb-3">
                <InfiniteLoading @infinite="infiniteHandler" spinner="spiral">
                    <div slot="spinner">
                        <Spinner />
                        <span class="infinite-loader">Meer resultaten laden</span>
                    </div>
                    <div slot="no-more"></div>
                    <div slot="no-results"></div>
                </InfiniteLoading>
            </div>
        </div>
        <div v-else class="bg-white text-center pt-5 pb-5">Geen resultaten.</div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { accessRequestApi } from '@dbco/portal-api';
import type { AccessRequestOverviewResponse } from '@dbco/portal-api/accessRequest.dto';
import InfiniteLoading from 'vue-infinite-loading';
import { Spinner } from '@dbco/ui-library';
const itemsPerPage = 50;

const accessRequestActions = {
    show: 'bekijken',
    exported: 'downloaden',
    caseDeleteStarted: 'verwijderen gestart',
    caseDeleteRecovered: 'herstellen',
    contactDeleteStarted: 'verwijderen gestart',
    contactDeleteRecovered: 'herstellen',
};

type ActionKeys = keyof typeof accessRequestActions;

export default defineComponent({
    name: 'AccessRequestsTable',
    components: { InfiniteLoading, Spinner },
    data() {
        return {
            isLoading: true,
            accessRequests: [] as AccessRequestOverviewResponse[],
            limit: itemsPerPage,
        };
    },
    async created() {
        const { data } = await accessRequestApi.getOverview();
        this.accessRequests = data;
        this.isLoading = false;
    },
    computed: {
        visibleAccessRequests() {
            return this.accessRequests.slice(0, this.limit);
        },
    },
    methods: {
        actions(accessRequest: AccessRequestOverviewResponse) {
            const actions = (Object.keys(accessRequestActions) as ActionKeys[])
                .filter((key) => accessRequest[key] > 0)
                .map((key) => accessRequestActions[key]);

            if (!actions.length) return '';

            const actionsString = actions.join(', ');

            return actionsString[0].toUpperCase() + actionsString.slice(1);
        },
        infiniteHandler($state: any) {
            this.limit += itemsPerPage;
            if (this.limit >= this.accessRequests.length) {
                $state.complete();
            } else {
                $state.loaded();
            }
        },
    },
});
</script>

<style lang="scss" scoped>
.table-ggd tbody {
    tr td:first-child {
        padding: 12px;
    }

    tr td a {
        color: inherit;
    }
}
</style>
