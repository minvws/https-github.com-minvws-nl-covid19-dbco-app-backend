<template>
    <div>
        <div v-if="loading" class="my-5">
            <p class="text-center">
                <BSpinner small />
            </p>
        </div>
        <div v-else-if="!loading && timelineItems.length === 0" class="my-5">
            <p>{{ $t(`components.covidCaseHistory.no_history`) }}</p>
        </div>

        <div v-else class="history-item-container">
            <Card
                v-for="(item, index) in timelineItems"
                :key="item.uuid"
                class="history-item-container"
                :title="item.title"
            >
                <div class="history-item" :class="{ 'with-details': !!item.call_to_action_uuid }">
                    <div>
                        <template v-if="item.note">
                            <!-- eslint-disable vue/no-v-html : data provided by backend, no XSS risk -->
                            <p
                                class="note-description"
                                v-if="item.timelineable_type === CaseTimelineType.CaseAssignmentHistory"
                                data-testid="note"
                                v-html="item.note"
                            ></p>
                            <p class="note-description" v-else data-testid="note">{{ item.note }}</p>
                        </template>
                        <div v-if="item.call_to_action_deadline" class="note-deadline">
                            <p>{{ $t(`components.covidCaseHistory.note_deadline`) }}</p>
                            <p>{{ item.call_to_action_deadline }}</p>
                        </div>
                        <p v-if="item.author_user" class="note-author">{{ item.author_user }}</p>

                        <p class="time-stamp">
                            {{ item.time }}
                            <template v-if="item.timelineable_type === CaseTimelineType.CallToAction">
                                • {{ $t(`components.covidCaseHistory.created_at`) }}</template
                            >
                        </p>

                        <div v-if="item.timelineable_type === CaseTimelineType.ExpertQuestion" class="answer">
                            <p v-if="item.answer">{{ item.answer }}</p>
                            <p v-else class="no-answer text-center mb-0">
                                {{ $t(`components.covidCaseHistory.no_answer`) }}
                            </p>
                            <p v-if="item.answer && item.answer_user" class="note-author">{{ item.answer_user }}</p>
                            <p v-if="item.answer && item.answer_time" class="time-stamp">{{ item.answer_time }}</p>
                        </div>
                    </div>
                    <Collapse
                        v-if="item.call_to_action_items && item.call_to_action_items.length"
                        :label-open="$tc(`components.covidCaseHistory.hide_details`)"
                        :label-closed="$tc(`components.covidCaseHistory.show_details`)"
                    >
                        <div class="history-item-details pt-4">
                            <div v-for="cta_item in item.call_to_action_items" class="history-item">
                                <div>
                                    <p v-if="cta_item.note">{{ cta_item.note }}</p>
                                    <p class="note-title">
                                        {{ translatedActionWithUser(cta_item) }}
                                    </p>
                                    <small class="time-stamp">{{ cta_item.datetime }}</small>
                                </div>
                            </div>
                        </div>
                    </Collapse>
                </div>
            </Card>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { caseApi } from '@dbco/portal-api';
import type { CaseTimelineDTO } from '@dbco/portal-api/case.dto';
import { CaseTimelineType } from '@dbco/portal-api/case.dto';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import type { CallToActionHistoryItem } from '@dbco/portal-api/callToAction.dto';
import type { TranslateResult } from 'vue-i18n';
import { Card, Collapse } from '@dbco/ui-library';

interface Data {
    CaseTimelineType: typeof CaseTimelineType;
    loading: boolean;
    timelineItems: CaseTimelineDTO[];
}

export default defineComponent({
    name: 'CovidCaseHistory',
    components: {
        Card,
        Collapse,
    },
    props: {
        selectedCaseUuid: {
            type: String,
            required: true,
        },
        plannerTimeline: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            CaseTimelineType,
            loading: true,
            timelineItems: [],
        } as Data;
    },
    methods: {
        translatedActionWithUser(item: CallToActionHistoryItem): TranslateResult {
            const user = item.user;
            const translatedRoles = user.roles.map((role) => this.$t(`roles.${role}`)).join(', ');
            const nameAndRoles = [user.name, translatedRoles].filter(Boolean).join(', ');

            return this.$t(`components.callToActionSidebar.history.action.${item.callToActionEvent}`, { nameAndRoles });
        },
    },
    async created() {
        if (this.$props.plannerTimeline) {
            this.timelineItems = await caseApi.getCaseTimelinePlanner(this.selectedCaseUuid);
        } else {
            this.timelineItems = await caseApi.getCaseTimeline(this.selectedCaseUuid);
        }
        this.timelineItems = await useCallToActionStore().addHistoryItemsToTimeline(this.timelineItems);
        this.loading = false;
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.history-item {
    .note-title {
        font-weight: 500;
        font-size: 16px;
        margin-bottom: 0.65rem;
    }

    .note-description {
        margin-bottom: $padding-xs;
    }

    .note-author {
        margin-bottom: 0.65rem;
    }

    .note-author,
    .note-deadline p:first-child {
        font-weight: 500;
    }

    .note-deadline p:first-child {
        margin-bottom: 0;
    }

    .note-deadline p:last-child {
        margin-bottom: $padding-xs;
    }

    .time-stamp,
    .no-answer {
        color: $dark-grey;
        margin-bottom: 0;
    }

    .answer {
        background-color: $bco-grey;
        padding: $padding-sm;
        margin: $padding-sm 0;
        border-radius: $border-radius-small;
    }

    &:last-child {
        border-bottom: none;
    }
}

.history-item-details {
    display: flex;
    flex-direction: column;
    gap: $padding-sm;

    .history-item {
        background-color: $input-grey;
        border-radius: $border-radius-small;
        padding: $padding-sm;

        div {
            display: flex;
            flex-direction: column;
            gap: $padding-xs;

            p {
                margin-bottom: 0;
            }

            .note-title {
                font-size: inherit;
            }
            .no-answer {
                margin-bottom: $padding-xs;
            }
        }
    }
}
</style>
