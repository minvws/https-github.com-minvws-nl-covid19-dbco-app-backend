<template>
    <DatePicker
        v-model="context.model"
        :editable="editable"
        :disabled="disabled"
        :ranges="ranges"
        :rangeCutOff="rangeCutOff"
        :singleSelection="singleSelection"
        @input="(val) => $emit('change', val)"
    >
        <template v-slot:alert>
            <slot />
        </template>
    </DatePicker>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import type { VueFormulateContext } from '../ts/formTypes';
import DatePicker from '@/components/formControls/DatePicker/DatePicker.vue';
import type { CalendarViewV1 } from '@dbco/enum';

export default defineComponent({
    name: 'FormDatePicker',
    components: { DatePicker },
    props: {
        context: {
            type: Object as PropType<VueFormulateContext>,
            required: true,
        },
        calendarView: {
            type: String as PropType<CalendarViewV1>,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        editable: {
            type: Boolean,
            default: true,
        },
        rangeCutOff: {
            type: [Date, String] as PropType<Date | string | null>,
            default: null,
        },
        singleSelection: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        ranges() {
            return useCalendarStore().getCalendarDataByView(this.calendarView);
        },
    },
});
</script>
