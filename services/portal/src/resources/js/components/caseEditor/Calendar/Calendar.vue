<template>
    <div data-testid="calendar" class="calendar" tabindex="0">
        <BCardBody class="calendar__container">
            <slot name="alert" />
            <div class="container__months">
                <div class="month-item-weekdays-row">
                    <div title="maandag">M</div>
                    <div title="dinsdag">D</div>
                    <div title="woensdag">W</div>
                    <div title="donderdag">D</div>
                    <div title="vrijdag">V</div>
                    <div title="zaterdag">Z</div>
                    <div title="zondag">Z</div>
                </div>
                <div class="container__days">
                    <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
                    <div
                        v-for="(day, index) in visibleDays"
                        :key="`day_${index}`"
                        class="day-item"
                        :class="periodClasses(day)"
                        :style="inRange(day)"
                        @click="toggleDay(day)"
                        :data-id="periodIdentifiers(day)"
                    >
                        <div :class="pointClasses(day)" :style="inPoint(day)" :data-id="pointIdentifiers(day)">
                            <span v-if="index === 0 || day.getDate() === 1" class="inline-month">{{
                                $filters.dateFnsFormat(day, 'MMM')
                            }}</span>
                            <span :class="{ 'day-below-inline-month': index === 0 || day.getDate() === 1 }">{{
                                $filters.dateFnsFormat(day, 'd')
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </BCardBody>
        <BCardBody v-if="showLegend" class="calendar__legend">
            <div v-for="(label, index) in legendRanges" :key="`label_${index}`" class="calendar__legend__item">
                <span class="legend" :style="{ backgroundColor: rangeColors[label.color] }"></span> {{ label.label }}
            </div>
        </BCardBody>
    </div>
</template>

<script lang="ts">
/* eslint-disable vuejs-accessibility/click-events-have-key-events */

import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { min, isSameDay, startOfDay } from 'date-fns';
import { isBetweenDays } from '@/utils/date';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import { legendRanges, maxDate, rangeColors, rangeOverlapDates, visibleDays } from '@/utils/calendar';
import { CalendarPointColorV1 } from '@dbco/enum';

export default defineComponent({
    name: 'Calendar',
    data() {
        return {
            rangeColors,
        };
    },
    props: {
        defaultMaxDate: {
            type: [Date, String] as PropType<Date | string | null>,
            default: null,
        },
        selectedDays: {
            type: Array as PropType<string[]>,
            default: () => [],
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
            default: false,
        },
        showLegend: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        selectedDates() {
            return this.selectedDays.map((dateString) => new Date(dateString));
        },
        rangeStartDates() {
            return this.normalizedRanges.map((range) => range.startDate);
        },
        normalizedRanges() {
            return Array.isArray(this.ranges) ? this.ranges : this.ranges();
        },
        periods() {
            return this.normalizedRanges.filter((range) => range.type === 'period');
        },
        points() {
            const selectedDays: CalendarDateRange[] = this.selectedDays.map((selectedDateStr) => {
                return {
                    id: selectedDateStr,
                    type: 'point',
                    startDate: new Date(selectedDateStr),
                    endDate: new Date(selectedDateStr),
                    color: CalendarPointColorV1.VALUE_purple,
                };
            });

            return this.normalizedRanges.filter((range) => range.type === 'point').concat(selectedDays);
        },
        minDate() {
            // earliest date to render is the start of the week for
            // the earliest range start date, selected date or todays date
            return min([...this.rangeStartDates, ...this.selectedDates, new Date()]);
        },
        maxDate() {
            return maxDate(this.normalizedRanges, this.rangeCutOff, this.defaultMaxDate);
        },
        visibleDays() {
            return visibleDays(this.rangeStartDates, this.selectedDates, this.maxDate);
        },
        rangeOverlapDates() {
            return rangeOverlapDates(this.normalizedRanges);
        },
        legendRanges() {
            return legendRanges(this.normalizedRanges);
        },
    },
    methods: {
        inPoint(date: Date) {
            let styleObject: AnyObject = {};
            this.points.forEach((range) => {
                if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    styleObject.background = rangeColors[range.color];
                }
            });

            return styleObject;
        },
        inRange(date: Date) {
            let styleObject: AnyObject = {};
            this.periods.forEach((range) => {
                if (this.isRangeOverlap(date)) {
                    const ranges = this.rangeOverlapDates.find((overlapRange) => overlapRange.start.endDate);
                    if (ranges) {
                        styleObject.backgroundImage = `linear-gradient(315deg, ${rangeColors[ranges.start.color]}, ${
                            rangeColors[ranges.start.color]
                        } 50%, ${rangeColors[ranges.end.color]} 50%, ${rangeColors[ranges.end.color]})`;
                    }
                } else if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    styleObject.background = rangeColors[range.color];
                }
            });

            return styleObject;
        },
        pointClasses(date: Date) {
            let classes: string[] = [];
            const today = startOfDay(new Date());

            this.points.forEach((range) => {
                if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    classes.push('is-point');
                }
            });

            if (date > today) {
                classes.push('is-locked');
            }

            if (!this.editable || !isBetweenDays(date, this.minDate, this.maxDate, '[]')) {
                classes.push('is-locked');
            }

            return classes;
        },
        periodClasses(date: Date) {
            let classes: string[] = [];
            const today = startOfDay(new Date());

            this.periods.forEach((range) => {
                if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    classes.push('in-period');
                }

                if (isSameDay(date, range.startDate)) {
                    classes.push('start-period');
                }

                if (isSameDay(date, range.endDate)) {
                    classes.push('end-period');
                }

                if (this.isRangeOverlap(date)) {
                    classes.push('is-overlap');
                }
            });

            if (date > today) {
                classes.push('is-locked');
            }

            if (!this.editable || !isBetweenDays(date, this.minDate, this.maxDate, '[]')) {
                classes.push('is-locked');
            }

            return classes;
        },
        periodIdentifiers(date: Date) {
            let identifiers: string[] = [];

            this.periods.forEach((range) => {
                if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    identifiers.push(range.id);
                }
            });

            return identifiers;
        },
        pointIdentifiers(date: Date) {
            let identifiers: string[] = [];

            this.points.forEach((range) => {
                if (isBetweenDays(date, range.startDate, range.endDate, '[]')) {
                    identifiers.push(range.id);
                }
            });

            return identifiers;
        },
        toggleDay(date: Date) {
            if (this.editable) {
                if (isBetweenDays(date, this.minDate, this.maxDate, '[]')) {
                    this.$emit('onToggleDay', date);
                }
            }
        },
        isRangeOverlap(date: Date) {
            return this.rangeOverlapDates.some((overlap) => isSameDay(date, overlap.start.startDate));
        },
    },
});
</script>
