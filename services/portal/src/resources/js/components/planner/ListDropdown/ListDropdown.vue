<template>
    <div>
        <BDropdown
            ref="listDropdownRef"
            class="list-dropdown"
            toggle-class="fab fab--white ml-2"
            menu-class="dropdown-menu-center"
            size="lg"
            variant="link"
            v-on:show="onDropdownShow"
            no-caret
        >
            <template #button-content>
                <i class="icon icon--hamburger ml-1 mr-2" />
                Lijsten
            </template>
            <BTable
                class="lists-table m-0"
                borderless
                fixed
                :fields="fields"
                :items="[allCasesListOption, ...lists]"
                @row-clicked="onListSelect"
                :aria-rowcount="totalRowsCount"
            >
                <template #cell(selected)="data">
                    <i v-if="data.item.uuid === selectedList" class="icon icon--sm icon--checkmark icon--m0" />
                </template>
                <template #cell(name)="data">
                    <span class="ellipsis">{{ data.item.name }}</span>
                    <BButton
                        v-if="data.item.uuid"
                        class="p-0"
                        variant="link"
                        size="sm"
                        @click="() => onEditList(data.item)"
                    >
                        Bewerk
                    </BButton>
                </template>
                <template #cell()="data">
                    <BSkeleton
                        v-if="showSkeletonForList(data)"
                        animation="fade"
                        class="count-skeleton"
                        no-aspect
                    ></BSkeleton>
                    <span v-else>{{ data.item[data.field.key] }}</span>
                </template>
            </BTable>
            <InfiniteLoading :identifier="infiniteId" @infinite="onListsInfinite" spinner="spiral">
                <div slot="spinner">
                    <Spinner />
                    <span class="infinite-loader">Meer lijsten laden...</span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
            <hr class="mb-2 mt-0" />
            <div class="dropdown-footer">
                <BButton block variant="primary" @click="onCreateList"> Nieuwe lijst maken </BButton>
            </div>
        </BDropdown>
    </div>
</template>
<script lang="ts">
import { caseListApi } from '@dbco/portal-api';
import type { CaseListWithStats, CaseList } from '@dbco/portal-api/caseList.dto';
import env from '@/env';
import type { PropType } from 'vue';
import { defineComponent, ref, computed, unref } from 'vue';
import type { BDropdown } from 'bootstrap-vue';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import type { CancelToken, CancelTokenSource } from 'axios';
import axios from 'axios';
import { usePlanner } from '@/store/planner/plannerStore';
import { CaseListStatsMode } from '@dbco/portal-api/client/caseList.api';
import { Spinner } from '@dbco/ui-library';

type AllCasesList = Omit<CaseListWithStats | CaseList, 'uuid'> & {
    uuid: null;
};
type List = CaseListWithStats | CaseList | AllCasesList;
type Lists = List[];

const fields = [
    {
        key: 'selected',
        label: '',
        thStyle: { width: '24px' },
    },
    {
        key: 'name',
        label: 'Naam',
        tdClass: 'title d-flex justify-content-between align-items-center',
        thStyle: { width: '240px' },
    },
    {
        key: 'unassignedCasesCount',
        label: 'Te verdelen',
        thClass: 'text-center',
        thStyle: { width: '120px' },
        tdClass: 'text-center',
    },
    {
        key: 'assignedCasesCount',
        label: 'Toegewezen',
        thClass: 'text-center',
        thStyle: { width: '120px' },
        tdClass: 'text-center',
    },
    {
        key: 'completedCasesCount',
        label: 'Te controleren',
        thClass: 'text-center',
        thStyle: { width: '120px' },
        tdClass: 'text-center',
    },
    {
        key: 'archivedCasesCount',
        label: 'Gesloten',
        thClass: 'text-center',
        thStyle: { width: '120px' },
        tdClass: 'text-center',
    },
];

export default defineComponent({
    props: {
        list: { type: Object as PropType<List>, required: false },
    },
    emits: ['selected', 'editList', 'createList'],
    components: {
        InfiniteLoading,
        Spinner,
    },
    // Lesson: Do not destructure the props argument.. It seems to break reactiveness...
    setup(props, ctx) {
        const statsMode = env?.isPlannerGetCaselistsStats0Enabled
            ? CaseListStatsMode.Disabled
            : CaseListStatsMode.Enabled;

        const listDropdownRef = ref<BDropdown | null>(null);
        const listPages = ref<Lists[]>([]);
        const currentPage = ref<number>(1);
        const cancelToken = ref<CancelTokenSource | null>(null);
        const infiniteId = ref<number>(Date.now());
        const totalRowsCount = ref<number>(0);
        const selectedList = computed(() => props.list?.uuid || null);

        const planner = usePlanner();

        const allCasesListOption = computed(() => {
            const {
                assigned = 0,
                queued = 0,
                completed = 0,
                archived = 0,
                unassigned = 0,
            } = unref(planner.allCaseCounts) || {};

            return {
                uuid: null,
                name: 'Alle cases',
                assignedCasesCount: assigned,
                unassignedCasesCount: queued + unassigned,
                completedCasesCount: completed,
                archivedCasesCount: archived,
            };
        });

        const showSkeletonForList = (data: any) => {
            // All cases list option.
            if (data.item.uuid == null) {
                return planner.allCaseCounts ? false : true;
            }
            // Don't show skeleton if stats will never show up.
            if (statsMode === CaseListStatsMode.Disabled) {
                return false;
            }
            return data.item[data.field.key] == null ? true : false;
        };

        const onListsInfinite = async ($state: StateChanger) => {
            const initialListPage = currentPage.value;

            // Always do initial fetch without stats for better performance
            const { data, lastPage, total } = await caseListApi.getLists(
                CaseListStatsMode.Disabled,
                'list',
                initialListPage
            );
            listPages.value.push(data);
            totalRowsCount.value = total;

            if (lastPage !== listPages.value.length) {
                currentPage.value += 1;
                $state?.loaded();
            } else {
                $state?.complete();
            }

            // Update with stats after initial render of lists
            if (statsMode === CaseListStatsMode.Enabled) {
                cancelToken.value = axios.CancelToken.source();
                const { data } = await caseListApi.getLists(
                    CaseListStatsMode.Enabled,
                    'list',
                    initialListPage,
                    cancelToken.value?.token as CancelToken
                );
                listPages.value.splice(initialListPage - 1, 1, data);
            }
        };

        const onDropdownShow = () => {
            // Check if there are any previous pending requests
            if (cancelToken.value) {
                cancelToken.value.cancel('Operation canceled due to new request.');
            }
            currentPage.value = 1;
            infiniteId.value++;
            listPages.value = [];
        };

        const onListSelect = (list: List) => {
            ctx.emit('selected', list);
            listDropdownRef.value?.hide();
        };
        const onEditList = (data: List) => {
            ctx.emit('editList', data);
            listDropdownRef.value?.hide();
        };
        const onCreateList = () => {
            ctx.emit('createList');
            listDropdownRef.value?.hide();
        };

        const lists = computed(() => ([] as List[]).concat.apply([], listPages.value));

        return {
            listDropdownRef,
            fields,
            lists,
            allCasesListOption,
            infiniteId,
            selectedList,
            totalRowsCount,
            onListsInfinite,
            onListSelect,
            onEditList,
            onCreateList,
            showSkeletonForList,
            onDropdownShow,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.fab {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 2rem;
    height: 2rem;
    text-align: center;
    @apply tw-rounded-full;
    border: 1px solid $lightest-grey;

    &--white {
        background: white;
        color: $bco-purple;
    }
}

.ellipsis {
    overflow: hidden;
    text-overflow: ellipsis;
}

::v-deep .list-dropdown {
    .fab {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0.375rem;
        text-align: center;
        border: 1px solid #e6e6ef;
        font-weight: 500;

        &--white {
            background: white;
            color: $bco-purple;
        }

        .icon {
            background-position: center;
        }
    }

    .dropdown-menu {
        max-height: calc(90vh - 250px);
        overflow-y: auto;
        padding: 0;
        .lists-table {
            thead {
                th {
                    background: #fff;
                    padding: 8px 0;
                    font-weight: 500;
                    font-size: 14px;
                    line-height: 16px;
                    color: $lighter-grey;
                    text-transform: uppercase;
                    position: sticky;
                    top: 0;
                }
            }
            tbody {
                tr {
                    cursor: pointer;

                    td {
                        min-height: 40px;
                        color: $light-grey;
                        padding: 0 4px;
                        vertical-align: middle;

                        &:first-child {
                            padding: 0 4px 0 8px;
                        }

                        &:last-child {
                            padding: 0 8px 0 4px;
                        }

                        &.title {
                            span {
                                pointer-events: none;
                            }
                        }

                        .btn-link {
                            color: $primary;
                            visibility: hidden;
                        }
                    }

                    &:hover {
                        color: $primary;
                        background-color: $input-grey;

                        td {
                            &.title {
                                color: $primary;
                            }

                            .btn-link {
                                visibility: visible;
                            }
                        }
                    }
                }
            }
        }

        .dropdown-footer {
            padding: 8px 8px;
            position: sticky;
            bottom: 0px;
        }
    }
}
</style>
