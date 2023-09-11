import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import type { CalendarItem } from '@dbco/portal-api/admin.dto';
import { getMonday } from '@/utils/date';
import { add, eachDayOfInterval, isSameDay, max, min, nextSunday } from 'date-fns';
import {
    CalendarPeriodColorV1,
    CalendarPointColorV1,
    calendarPeriodColorV1Options,
    calendarPointColorV1Options,
} from '@dbco/enum';

export const maxDate = (
    ranges: CalendarDateRange[],
    rangeCutOff: Date | string | null,
    defaultMaxDate: Date | string | null
) => {
    const latestRangeDate = ranges.reduce<Date | null>((latestDate, range) => {
        return !latestDate || range.endDate > latestDate ? range.endDate : latestDate;
    }, null);

    // Ranges are leading
    if (latestRangeDate) {
        // max is the earliest date of: 1. latest range date, 2. given range cut off
        return min([latestRangeDate, rangeCutOff ? new Date(rangeCutOff) : latestRangeDate]);
    }

    // Otherwise default to the given max
    if (defaultMaxDate) {
        return new Date(defaultMaxDate);
    }

    // If no max is given, use today
    return new Date();
};

export const visibleDays = (rangeStartDates: Date[], selectedDates: Date[], maxDate: Date) => {
    const today = new Date();
    const firstVisibleDay = min([...rangeStartDates, ...selectedDates, today.setDate(today.getDate() - 21)]);
    const firstMonday = getMonday(firstVisibleDay);
    const lastSunday = nextSunday(maxDate);

    // the earliest allowed monday will limit the amount of visible days to be rendered
    const earliestAllowedMonday = getMonday(add(lastSunday, { weeks: -15 }));
    const mondayToUse = max([firstMonday, earliestAllowedMonday]);

    return eachDayOfInterval({ start: mondayToUse, end: lastSunday });
};

export const rangeOverlapDates = (ranges: CalendarDateRange[]) => {
    const periods = ranges.filter((r) => r.type === 'period');
    const overlaps: { start: CalendarDateRange; end: CalendarDateRange }[] = [];
    periods.forEach((startDatePeriod) => {
        const endDatePeriod = periods.find((endDatePeriod) => {
            return isSameDay(startDatePeriod.startDate, endDatePeriod.endDate);
        });

        if (endDatePeriod) {
            overlaps.push({
                start: startDatePeriod,
                end: endDatePeriod,
            });
        }
    });

    return overlaps;
};

export const legendRanges = (ranges: CalendarDateRange[]) => {
    return ranges.reduce((acc: CalendarDateRange[], range) => {
        // Filter out ranges without labels
        if (!range.label) return acc;

        // Filter out duplicate ranges by key
        if (acc.some((a) => a.key && range.key && a.key === range.key)) return acc;

        return [...acc, range];
    }, []);
};

export const colorName = (item: Partial<CalendarItem> | undefined) => {
    if (!item?.itemType) {
        const periodColor = calendarPeriodColorV1Options[item?.color as CalendarPeriodColorV1];
        return periodColor ?? calendarPointColorV1Options[item?.color as CalendarPointColorV1];
    }
    if (item.itemType === 'period') {
        return calendarPeriodColorV1Options[item.color as CalendarPeriodColorV1];
    }
    return calendarPointColorV1Options[item.color as CalendarPointColorV1];
};

export const colorOptionsForType = (item: Partial<CalendarItem> | undefined) => {
    if (!item?.itemType) {
        return { ...calendarPeriodColorV1Options, ...calendarPointColorV1Options };
    }
    if (item.itemType === 'period') {
        return calendarPeriodColorV1Options;
    }
    return calendarPointColorV1Options;
};

export const rangeColors: Record<CalendarPeriodColorV1 | CalendarPointColorV1, string> = {
    [CalendarPeriodColorV1.VALUE_light_red]: '#FEE9EA',
    [CalendarPeriodColorV1.VALUE_light_orange]: '#FF812033',
    [CalendarPeriodColorV1.VALUE_light_yellow]: '#FFEF99',
    [CalendarPeriodColorV1.VALUE_light_green]: '#D5F5DE',
    [CalendarPeriodColorV1.VALUE_light_blue]: '#DDF0FC',
    [CalendarPeriodColorV1.VALUE_light_purple]: '#F0EBFF',
    [CalendarPeriodColorV1.VALUE_light_lavender]: '#B092FF33',
    [CalendarPeriodColorV1.VALUE_light_pink]: '#E289B133',
    [CalendarPointColorV1.VALUE_red]: '#CD262D',
    [CalendarPointColorV1.VALUE_orange]: '#FF8120',
    [CalendarPointColorV1.VALUE_yellow]: '#C1A200',
    [CalendarPointColorV1.VALUE_green]: '#09B9A1',
    [CalendarPointColorV1.VALUE_azure_blue]: '#008DE4',
    [CalendarPointColorV1.VALUE_purple]: '#5616FF',
    [CalendarPointColorV1.VALUE_lavender]: '#B092FF',
    [CalendarPointColorV1.VALUE_pink]: '#E289B1',
};
