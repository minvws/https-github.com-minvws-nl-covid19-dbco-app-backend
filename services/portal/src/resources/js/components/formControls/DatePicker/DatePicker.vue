<template>
    <div class="datepicker" v-click-outside="outsideClick">
        <!-- eslint-disable-next-line vuejs-accessibility/no-static-element-interactions -->
        <div @mousedown.prevent="inputClick">
            <BFormInput
                :class="formInputClass"
                :disabled="disabled"
                data-testid="input-placeholder"
                @keydown="inputClick"
                :placeholder="singleSelection ? 'Kies datum' : 'Kies datum(s)'"
                :value="selectedDatesSummaryText"
            />
            <IconWarningSvg
                v-if="inputWarning"
                icon="warning"
                class="svg-icon--warning"
                v-b-tooltip.hover="inputWarning"
            />
        </div>
        <Calendar
            v-if="isOpen"
            @onToggleDay="toggleDay"
            :class="['calendar--dropdown', calendarClass ? calendarClass : '']"
            :selectedDays="selectedDays"
            :ranges="ranges"
            :rangeCutOff="rangeCutOff"
            :editable="editable"
            tabindex="0"
        >
            <template v-slot:alert>
                <slot name="alert" />
            </template>
        </Calendar>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { formatDate, getSummaryTextForDates } from '@/utils/date';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import { isSameDay } from 'date-fns';
import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
import IconWarningSvg from '@images/icon-warning.svg?vue';

export default defineComponent({
    name: 'DatePicker',
    components: { Calendar, IconWarningSvg },
    props: {
        inputClass: {
            type: [String, Object],
            default: null,
        },
        inputWarning: {
            type: String,
            required: false,
        },
        calendarClass: {
            type: String,
            default: null,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
        ranges: {
            type: [Array, Function] as PropType<Array<CalendarDateRange> | (() => Array<CalendarDateRange>)>,
            default: () => [],
        },
        rangeCutOff: {
            type: [Date, String] as PropType<Date | string | null>,
            default: null,
        },
        editable: {
            type: Boolean,
            default: true,
        },
        singleSelection: {
            type: Boolean,
            default: false,
        },
        value: {
            type: [String, Array] as PropType<string | string[]>, // STRING date OR array of dates STRINGS (so you can directly pass them from/to an api.
            default: () => [],
        },
    },
    data() {
        return {
            selectedDays: [] as string[],
            isOpen: false,
        };
    },
    watch: {
        value: {
            handler() {
                let val = this.value || [];
                // if we get a single value bound, turn it into an array for consistency
                if (!Array.isArray(val)) {
                    val = [val];
                }

                this.selectedDays = val.map((item) => formatDate(new Date(item), 'yyyy-MM-dd'));
            },
            immediate: true,
        },
        isOpen() {
            this.isOpen ? this.$emit('opened') : this.$emit('close', this.selectedDays);
        },
    },
    methods: {
        inputClick(e: MouseEvent) {
            if (this.disabled) return;
            if ((e as any).keyCode) {
                // only open using spacebar or return
                if (![32, 13].includes((e as any).keyCode)) return;

                // stop the key event on the input and remove focus
                e.preventDefault();
                if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
            }
            this.isOpen = !this.isOpen;
        },
        outsideClick() {
            this.isOpen = false;
        },
        toggleDay(date: Date) {
            const foundIndex = this.selectedDays.findIndex((selectedDate) => isSameDay(new Date(selectedDate), date));
            if (foundIndex >= 0) {
                this.selectedDays.splice(foundIndex, 1);
            } else {
                this.selectedDays.push(formatDate(date, 'yyyy-MM-dd'));
            }

            if (this.singleSelection) {
                // Make sure we only select one
                if (this.selectedDays.length > 1) {
                    this.selectedDays.splice(0, 1);
                }
                this.$emit('input', this.selectedDays.length > 0 ? this.selectedDays[0] : null);
                // For singleSelection, close the dropdown after selection.
                this.isOpen = false;
            } else {
                this.$emit('input', this.selectedDays);
            }
        },
    },
    computed: {
        selectedDatesSummaryText() {
            if (!this.selectedDays || this.selectedDays.length == 0) return null;
            // if singleSelection then include day name in rendering.
            if (this.singleSelection) {
                return this.$filters.dateFormatLong(this.selectedDays[0]) as any;
            }
            return getSummaryTextForDates(this.selectedDays);
        },
        formInputClass() {
            if (typeof this.inputClass === 'object') return { mdpbutton: true, 'bg-white': true, ...this.inputClass };
            return ['mdpbutton', 'bg-white', this.inputClass];
        },
    },
    beforeDestroy() {
        if (this.isOpen) {
            this.$emit('close', this.selectedDays);
        }
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.svg-icon--warning {
    position: absolute;
    right: 0.5625rem;
    top: 0.5625rem;
    height: 0.875rem;
    color: $bco-orange;
    flex-shrink: 0;
}

.datepicker {
    position: relative;
}
</style>
