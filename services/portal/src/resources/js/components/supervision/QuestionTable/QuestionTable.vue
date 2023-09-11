<template>
    <div id="question-table">
        <table class="table table-rounded table-hover table--clickable table-ggd table--align-start with-sort">
            <thead>
                <tr>
                    <th scope="col">Naam</th>
                    <th scope="col">Onderwerp</th>
                    <th scope="col">Regio (Case)</th>
                    <th scope="col" :aria-sort="ariaSort('createdAt')" @click="onSort('createdAt')">Tijdstip</th>
                    <th scope="col" :aria-sort="ariaSort('status')" @click="onSort('status')">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="question in supervisionQuestions"
                    :key="question.uuid"
                    class="custom-link"
                    :class="{ active: isActive(question.uuid), 'question-answered': question.answer }"
                    @click="selectUnansweredQuestion(question)"
                >
                    <th scope="row">{{ question.user.name }}</th>
                    <td class="wrap">{{ question.subject }}</td>
                    <td>{{ question.caseOrganisationName }}</td>
                    <td>
                        <time :datetime="parseDate(question.createdAt).toISOString()">{{
                            formatFromNow(parseDate(question.createdAt))
                        }}</time>
                    </td>
                    <td>{{ formattedStatus(question) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="mt-3 mb-3">
            <InfiniteLoading
                :identifier="supervisionQuestionTable.infiniteId"
                @infinite="onListsInfinite"
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
        <UpdateToast />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { parseDate, formatFromNow } from '@/utils/date';
import { StoreType } from '@/store/storeType';

import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import UpdateToast from '@/components/supervision/UpdateToast/UpdateToast.vue';
import { DefaultSortOptions } from '@dbco/portal-api/pagination';
import { mapActions, mapMutations, mapState } from '@/utils/vuex';
import { SupervisionActions } from '@/store/supervision/supervisionActions/supervisionActions';
import { SupervisionMutations } from '@/store/supervision/supervisionMutations/supervisionMutations';
import { Spinner } from '@dbco/ui-library';

export default defineComponent({
    name: 'QuestionTable',
    components: {
        InfiniteLoading,
        UpdateToast,
        Spinner,
    },
    data() {
        return {
            fields: [
                { label: 'naam', key: 'assignedUser.name' },
                { label: 'onderwerp', key: 'subject' },
                { label: 'regio', key: 'caseUuid' },
                { label: 'tijdstip', key: 'createdAt' },
                { label: 'status', key: 'answer' },
            ],
        };
    },
    computed: {
        ...mapState(StoreType.SUPERVISION, ['selectedQuestion', 'supervisionQuestions', 'supervisionQuestionTable']),
    },
    methods: {
        ...mapActions('supervision', {
            fetchQuestions: SupervisionActions.FETCH_QUESTIONS,
            selectQuestion: SupervisionActions.SELECT_QUESTION,
        }),
        ...mapMutations('supervision', {
            incrementTablePage: SupervisionMutations.INCREMENT_TABLE_PAGE,
            resetQuestionTable: SupervisionMutations.RESET_QUESTION_TABLE,
            setTableSort: SupervisionMutations.SET_TABLE_SORT,
        }),
        ariaSort(sort: `${DefaultSortOptions}`) {
            return this.supervisionQuestionTable.sort === sort && this.supervisionQuestionTable.order
                ? this.supervisionQuestionTable.order === 'asc'
                    ? 'ascending'
                    : 'descending'
                : 'none';
        },
        formatFromNow,
        formattedStatus(question: ExpertQuestionResponse) {
            if (question.answer) return this.$t('components.questionTable.status_answered_by_you');
            if (question.assignedUser) return this.$t('components.choreTable.status.picked_up_by_you');
            return this.$t('components.choreTable.status.not_yet_picked_up');
        },
        isActive(questionUuid: string) {
            return questionUuid === this.selectedQuestion?.uuid;
        },
        async onListsInfinite($state: StateChanger) {
            const lastPage = await this.fetchQuestions();
            if (lastPage !== this.supervisionQuestionTable.page) {
                this.incrementTablePage();
                $state?.loaded();
            } else {
                $state?.complete();
            }
        },
        onSort(sort: `${DefaultSortOptions}`) {
            if (this.supervisionQuestionTable.sort === sort && this.supervisionQuestionTable.order) {
                const newOrder = this.supervisionQuestionTable.order === 'asc' ? 'desc' : 'asc';
                this.setTableSort({ order: newOrder, sort: sort as DefaultSortOptions });
            } else {
                this.setTableSort({
                    order: sort === DefaultSortOptions.CREATED_AT ? 'desc' : 'asc',
                    sort: sort as DefaultSortOptions,
                });
            }
            this.resetQuestionTable();
        },
        parseDate,
        selectUnansweredQuestion(question: ExpertQuestionResponse) {
            if (question.answer) return;
            void this.selectQuestion(question.uuid);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#question-table {
    word-break: break-word;

    tr {
        &.active {
            background-color: $table-hover-bg;
        }

        &.question-answered {
            cursor: inherit;
            user-select: none;

            * {
                color: $lighter-grey !important;
            }

            &:hover {
                background-color: inherit;
            }
        }
    }

    th {
        min-width: 100px;
        vertical-align: top;
    }

    td {
        vertical-align: top;
        white-space: nowrap;

        &.wrap {
            white-space: normal;
        }

        &:not(:first-of-type) {
            color: $light-grey;
        }
    }
}
</style>
