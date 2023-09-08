<template>
    <div id="call-to-action-table">
        <table class="table table-rounded table-hover table--clickable table-ggd table--align-start with-sort">
            <thead>
                <tr>
                    <th scope="col">Onderwerp</th>
                    <th scope="col">Uitvoerdatum</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="callToAction in callToActions"
                    :key="callToAction.uuid"
                    class="custom-link"
                    :class="{ active: isActive(callToAction.uuid) }"
                    @click="select(callToAction)"
                >
                    <th scope="row">{{ callToAction.subject }}</th>
                    <td>
                        <!-- Format expiration date in UTC to mitigate daylight savings inconsistencies -->
                        {{ formatInOtherTimeZone(parseDate(callToAction.expiresAt), 'Europe/Lisbon', 'dd MMMM yyyy') }}
                        <i
                            v-if="checkExpiredDate(callToAction)"
                            aria-label="expired-date-icon"
                            class="icon icon--error-warning"
                        />
                    </td>
                    <td>{{ formattedStatus(callToAction) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="fullscreen-loader" v-if="loading">
            <p class="fullscreen-loader-text">{{ t(`components.callToActionSidebar.actions.pick_up_loading`) }}</p>
            <p><BSpinner big /></p>
        </div>

        <div class="mt-3 mb-3">
            <InfiniteLoading :identifier="callToActionTable.infiniteId" @infinite="onListsInfinite" spinner="spiral">
                <div slot="spinner">
                    <Spinner />
                    <span class="infinite-loader">{{ t('components.callToActionTable.load_more') }}</span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script lang="ts">
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import { formatInOtherTimeZone, parseDate } from '@/utils/date';
import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import { computed, defineComponent, unref } from 'vue';
import type { TranslateResult } from 'vue-i18n';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import useStatusAction, { isPending } from '@/store/useStatusAction';
import { useI18n } from 'vue-i18n-composable';
import { Spinner } from '@dbco/ui-library';

export default defineComponent({
    setup() {
        const store = useCallToActionStore();
        const callToActions = computed(() => store.tableContent);
        const callToActionTable = computed(() => store.table);
        const { status: selectStatus, action: selectAction } = useStatusAction(async (value: string) => {
            await store.select(value);
        });
        const { t } = useI18n();

        const formattedStatus = (callToAction: CallToActionResponse): TranslateResult => {
            if (callToAction.assignedUserUuid) return t('components.choreTable.status.picked_up_by_you');
            return t('components.choreTable.status.not_yet_picked_up');
        };

        const isActive = (ctaUuid: string): boolean => {
            return ctaUuid === store.selected?.uuid;
        };

        const onListsInfinite = async ($state: StateChanger) => {
            const table = unref(callToActionTable);
            const lastPage = await store.fetchTableContent();

            if (lastPage !== table.page) {
                store.incrementTablePage();
                $state?.loaded();
            } else {
                $state?.complete();
            }
        };

        const checkExpiredDate = (callToAction: CallToActionResponse) => {
            const now = new Date().getTime();
            const expirationDate = new Date(callToAction.expiresAt).getTime();
            return expirationDate < now;
        };

        const select = async (callToAction: CallToActionResponse): Promise<void> => {
            await selectAction(callToAction.uuid);
        };
        const loading = computed(() => isPending(selectStatus.value));

        return {
            callToActions,
            callToActionTable,
            formatInOtherTimeZone,
            formattedStatus,
            isActive,
            parseDate,
            checkExpiredDate,
            loading,
            select,
            onListsInfinite,
            t,
        };
    },
    components: {
        InfiniteLoading,
        Spinner,
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#call-to-action-table {
    word-break: break-word;

    td {
        vertical-align: top;
        white-space: nowrap;

        &:not(:first-of-type) {
            color: $light-grey;
        }
    }

    .custom-link.active {
        background-color: $table-hover-bg;
    }
}

.fullscreen-loader {
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;

    p {
        color: $bco-purple;
    }

    &-text {
        font-size: 0.875rem;
        font-weight: bold;
    }
}
</style>
