import { colorName, colorOptionsForType, legendRanges, maxDate, rangeOverlapDates, visibleDays } from '../calendar';
import { fakeCalendarDateRange } from '../__fakes__/calendarDateRange';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import { add, addDays, nextSunday, startOfDay, subDays } from 'date-fns';
import { formatDate, getMonday } from '../date';
import {
    CalendarItemV1,
    CalendarPeriodColorV1,
    CalendarPointColorV1,
    FixedCalendarPeriodV1,
    calendarPeriodColorV1Options,
    calendarPointColorV1Options,
} from '@dbco/enum';
import type { CalendarItem } from '@dbco/portal-api/admin.dto';

describe('calendar', () => {
    it('should return today as maxDate when no usable date is given', () => {
        const givenRanges: CalendarDateRange[] = [];

        const returnedMax = maxDate(givenRanges, null, null);

        const formattedMax = formatDate(returnedMax, 'yyy-MM-dd');
        const expectedDate = formatDate(new Date(), 'yyyy-MM-dd');
        expect(formattedMax).toStrictEqual(expectedDate);
    });

    it('should return given defaultMaxDate as maxDate when no other usable date is given', () => {
        const givenRanges: CalendarDateRange[] = [];
        const givenDefault = addDays(new Date(), 3);

        const returnedMax = maxDate(givenRanges, null, givenDefault);

        expect(returnedMax).toStrictEqual(givenDefault);
    });

    it('should return latest end date as maxDate when no cutoff is given', () => {
        const givenRange1 = fakeCalendarDateRange({ endDate: new Date() });
        const givenRange2 = fakeCalendarDateRange({ endDate: addDays(new Date(), 1) });
        const givenRange3 = fakeCalendarDateRange({ endDate: addDays(new Date(), 2) });

        const givenRanges = [givenRange1, givenRange2, givenRange3];

        const returnedMax = maxDate(givenRanges, null, null);

        expect(returnedMax).toStrictEqual(givenRange3.endDate);
    });

    it('should return latest end date as maxDate when the given cutoff is later', () => {
        const givenRange1 = fakeCalendarDateRange({ endDate: new Date() });
        const givenRange2 = fakeCalendarDateRange({ endDate: addDays(new Date(), 1) });
        const givenRange3 = fakeCalendarDateRange({ endDate: addDays(new Date(), 2) });
        const givenCutOff = addDays(new Date(), 3);

        const givenRanges = [givenRange1, givenRange2, givenRange3];

        const returnedMax = maxDate(givenRanges, givenCutOff, null);

        expect(returnedMax).toStrictEqual(givenRange3.endDate);
    });

    it('should return given cutoff as maxDate when the given cutoff is earlier than the latest end date', () => {
        const givenRange1 = fakeCalendarDateRange({ endDate: new Date() });
        const givenRange2 = fakeCalendarDateRange({ endDate: addDays(new Date(), 1) });
        const givenRange3 = fakeCalendarDateRange({ endDate: addDays(new Date(), 2) });
        const givenCutOff = addDays(new Date(), 1);

        const givenRanges = [givenRange1, givenRange2, givenRange3];

        const returnedMax = maxDate(givenRanges, givenCutOff, null);

        expect(returnedMax).toStrictEqual(givenCutOff);
    });

    const today = new Date();
    const firstMonday1 = startOfDay(getMonday(subDays(today, 21)));
    const lastSunday = startOfDay(nextSunday(today));
    const thirtyDaysAgo = subDays(today, 30);
    const firstMonday2 = startOfDay(getMonday(thirtyDaysAgo));
    const seventeenWeeksAgo = add(today, { weeks: -17 });
    const earliestAllowedMonday = startOfDay(getMonday(add(nextSunday(today), { weeks: -15 })));

    it.each<[Date, Date, Date, Date]>([
        [today, today, firstMonday1, lastSunday],
        [thirtyDaysAgo, today, firstMonday2, lastSunday],
        [seventeenWeeksAgo, today, earliestAllowedMonday, lastSunday],
    ])('%s', (givenStartDate, givenMaxDate, expectedStartDate, expectedEndDate) => {
        const returnedVisibleDays = visibleDays([givenStartDate], [], givenMaxDate);

        expect(returnedVisibleDays.at(0)).toStrictEqual(expectedStartDate);
        expect(returnedVisibleDays.at(-1)).toStrictEqual(expectedEndDate);
    });

    it('should return empty array when 1 of "source" or "infectious" ranges is not given', () => {
        const givenSourceRange = fakeCalendarDateRange({
            startDate: subDays(today, 20),
            endDate: subDays(today, 15),
            key: FixedCalendarPeriodV1.VALUE_source,
        });
        const givenEpisodeRange = fakeCalendarDateRange({
            startDate: addDays(today, 20),
            endDate: addDays(today, 15),
            key: FixedCalendarPeriodV1.VALUE_contagious,
        });

        const givenRanges = [givenSourceRange, givenEpisodeRange];

        const returnedOverlapDates = rangeOverlapDates(givenRanges);

        expect(returnedOverlapDates.length).toBe(0);
    });

    it('should return endDate of "source" period when the startDate of the "infectious" period is the same date', () => {
        const givenSourceRange = fakeCalendarDateRange({
            endDate: today,
            key: FixedCalendarPeriodV1.VALUE_source,
            startDate: subDays(today, 5),
        });
        const givenInfectiousRange = fakeCalendarDateRange({
            endDate: addDays(today, 5),
            key: FixedCalendarPeriodV1.VALUE_contagious,
            startDate: today,
        });

        const givenRanges = [givenSourceRange, givenInfectiousRange];

        const returnedOverlapDates = rangeOverlapDates(givenRanges);

        expect(returnedOverlapDates.length).toBe(1);
        expect(returnedOverlapDates[0].start).toStrictEqual(givenInfectiousRange);
    });

    it('should filter out duplicate ranges and ranges without labels for the legends', () => {
        const givenRanges = [
            fakeCalendarDateRange({ key: FixedCalendarPeriodV1.VALUE_source, label: 'test' }),
            fakeCalendarDateRange({ key: FixedCalendarPeriodV1.VALUE_source, label: 'test' }),
            fakeCalendarDateRange(),
            fakeCalendarDateRange({ label: '' }),
        ];

        const returnedLegendRanges = legendRanges(givenRanges);

        expect(returnedLegendRanges.length).toBe(2);
    });

    it.each<[Partial<CalendarItem>, string]>([
        [{ color: CalendarPeriodColorV1.VALUE_light_blue }, calendarPeriodColorV1Options.light_blue],
        [{ color: CalendarPointColorV1.VALUE_orange }, calendarPointColorV1Options.orange],
        [
            { color: CalendarPeriodColorV1.VALUE_light_blue, itemType: CalendarItemV1.VALUE_period },
            calendarPeriodColorV1Options.light_blue,
        ],
        [
            { color: CalendarPointColorV1.VALUE_orange, itemType: CalendarItemV1.VALUE_point },
            calendarPointColorV1Options.orange,
        ],
    ])('%s', (givenItem, expectedName) => {
        const returnedColorName = colorName(givenItem);
        expect(returnedColorName).toStrictEqual(expectedName);
    });

    it.each<[Partial<CalendarItem>, typeof calendarPeriodColorV1Options | typeof calendarPointColorV1Options]>([
        [{}, { ...calendarPeriodColorV1Options, ...calendarPointColorV1Options }],
        [{ itemType: CalendarItemV1.VALUE_period }, calendarPeriodColorV1Options],
        [{ itemType: CalendarItemV1.VALUE_point }, calendarPointColorV1Options],
    ])('%s', (givenItem, expectedOptions) => {
        const returnedColorOptions = colorOptionsForType(givenItem);
        expect(returnedColorOptions).toStrictEqual(expectedOptions);
    });
});
