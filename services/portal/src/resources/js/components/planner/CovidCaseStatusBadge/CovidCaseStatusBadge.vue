<template>
    <BBadge v-b-tooltip.hover :variant="status.variant" :title="status.tooltip">{{ status.title }}</BBadge>
</template>

<script lang="ts">
import { BcoStatusV1, ContactTracingStatusV1, contactTracingStatusV1Options } from '@dbco/enum';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

// Prevent duplicate definitions of the same status objects (Sigrid complaint) and define possibilities first.
const bcoFinishedStatus = {
    variant: 'success',
    title: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_bco_finished],
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_bco_finished],
};
const callbackRequestStatus = {
    variant: 'danger',
    title: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_callback_request],
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_callback_request],
};
const closedStatus = {
    variant: 'light-grey',
    title: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_closed],
    tooltip: 'Deze case is gesloten',
};
const completedStatus = {
    variant: 'success',
    title: 'Controleren',
    tooltip: 'Deze case moet gecontroleerd worden',
};
const fourTimesNotReachedStatus = {
    variant: 'warning',
    title: '4x geen gehoor',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_four_times_not_reached],
};
const looseEndStatus = {
    variant: 'danger',
    title: 'Los eindje',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_loose_end],
};
const newStatus = {
    variant: 'primary',
    title: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_new],
    tooltip: 'Deze case is nieuw',
};
const notApproachedStatus = {
    variant: 'danger',
    title: 'Niet benaderd',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_approached],
};
const notReachableStatus = {
    variant: 'danger',
    title: 'Onbereikbaar',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_reachable],
};
const notStartedStatus = {
    variant: 'primary',
    title: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_not_started],
    tooltip: 'Nieuwe case: BCO nog niet begonnen',
};
const startedStatus = {
    variant: 'warning',
    title: 'Indexgesprek',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_conversation_started],
};
const twiceNotReachedStatus = {
    variant: 'danger',
    title: '2x geen gehoor',
    tooltip: contactTracingStatusV1Options[ContactTracingStatusV1.VALUE_two_times_not_reached],
};

export default defineComponent({
    name: 'CovidCaseStatusBadge',
    props: {
        bcoStatus: {
            type: String as PropType<`${BcoStatusV1}` | null>,
            required: false,
        },
        statusIndexContactTracing: {
            type: String,
            required: false,
        },
    },
    computed: {
        status() {
            if (this.bcoStatus === BcoStatusV1.VALUE_archived) {
                return closedStatus;
            }
            switch (this.statusIndexContactTracing) {
                case ContactTracingStatusV1.VALUE_bco_finished:
                    return bcoFinishedStatus;
                case ContactTracingStatusV1.VALUE_callback_request:
                    return callbackRequestStatus;
                case ContactTracingStatusV1.VALUE_closed:
                    return closedStatus;
                case ContactTracingStatusV1.VALUE_closed_no_collaboration:
                case ContactTracingStatusV1.VALUE_closed_outside_ggd:
                    return completedStatus;
                case ContactTracingStatusV1.VALUE_completed:
                    return completedStatus;
                case ContactTracingStatusV1.VALUE_conversation_started:
                    return startedStatus;
                case ContactTracingStatusV1.VALUE_four_times_not_reached:
                    return fourTimesNotReachedStatus;
                case ContactTracingStatusV1.VALUE_loose_end:
                    return looseEndStatus;
                case ContactTracingStatusV1.VALUE_new:
                    return newStatus;
                case ContactTracingStatusV1.VALUE_not_approached:
                    return notApproachedStatus;
                case ContactTracingStatusV1.VALUE_not_reachable:
                    return notReachableStatus;
                case ContactTracingStatusV1.VALUE_not_started:
                    return notStartedStatus;
                case ContactTracingStatusV1.VALUE_two_times_not_reached:
                    return twiceNotReachedStatus;
                default:
                    return newStatus;
            }
        },
    },
});
</script>
