import { fakerjs } from '@/utils/test';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import { useCalendarStore } from './calendarStore';
import { createPinia, setActivePinia } from 'pinia';
import { CalendarPeriodColorV1, CalendarPointColorV1, CalendarViewV1, FixedCalendarPeriodV1 } from '@dbco/enum';

setActivePinia(createPinia());

describe('calendarStore', () => {
    const givenEndDate = fakerjs.date.soon();
    const givenStartDate = fakerjs.date.past();

    const expectedContagiousRange: CalendarDateRange = {
        id: 'contagious',
        type: 'period',
        key: FixedCalendarPeriodV1.VALUE_contagious,
        startDate: givenStartDate,
        endDate: givenEndDate,
        label: 'Besmettelijke periode',
        color: CalendarPeriodColorV1.VALUE_light_pink,
    };

    const expectedSourceRange: CalendarDateRange = {
        id: 'source',
        type: 'period',
        key: FixedCalendarPeriodV1.VALUE_source,
        startDate: givenStartDate,
        endDate: givenEndDate,
        label: 'Bronperiode',
        color: CalendarPeriodColorV1.VALUE_light_blue,
    };

    const expectedDateOfSymptomOnset: CalendarDateRange = {
        id: 'dateOfSymptomOnset',
        type: 'point',
        endDate: givenStartDate,
        startDate: givenStartDate,
        label: 'Eerste ziektedag',
        color: CalendarPointColorV1.VALUE_red,
    };

    const expectedDateOfTest: CalendarDateRange = {
        id: 'dateOfTest',
        type: 'point',
        endDate: givenStartDate,
        startDate: givenStartDate,
        label: 'Testdatum',
        color: CalendarPointColorV1.VALUE_orange,
    };

    const expectedVisitRanges: CalendarDateRange[] = [
        {
            id: 'contextVisistedRanges',
            type: 'point',
            endDate: givenStartDate,
            startDate: givenStartDate,
            label: 'Contactdatum(s)',
            color: CalendarPointColorV1.VALUE_purple,
        },
        {
            id: 'contextVisistedRanges',
            type: 'point',
            endDate: givenEndDate,
            startDate: givenEndDate,
            label: 'Contactdatum(s)',
            color: CalendarPointColorV1.VALUE_purple,
        },
    ];

    afterEach(() => {
        vi.resetAllMocks();
    });

    it('should return calendarDate by view', () => {
        const store = useCalendarStore();
        Object.assign(store.calendarViews, {
            [CalendarViewV1.VALUE_index_sidebar]: ['source', 'contagious', 'dateOfSymptomOnset', 'dateOfTest'],
        });

        vi.spyOn(store, 'calendarData', 'get').mockImplementation(() => [
            expectedSourceRange,
            expectedContagiousRange,
            expectedDateOfSymptomOnset,
            expectedDateOfTest,
        ]);

        expect(store.getCalendarDataByView(CalendarViewV1.VALUE_index_sidebar)).toStrictEqual([
            expectedSourceRange,
            expectedContagiousRange,
            expectedDateOfSymptomOnset,
            expectedDateOfTest,
        ]);
    });

    it('should return calendarDateItem by key', () => {
        const store = useCalendarStore();

        vi.spyOn(store, 'calendarData', 'get').mockImplementation(() => [
            expectedSourceRange,
            expectedContagiousRange,
            expectedDateOfSymptomOnset,
            expectedDateOfTest,
        ]);

        expect(
            store.getCalendarDateItemsByKey([
                FixedCalendarPeriodV1.VALUE_source,
                FixedCalendarPeriodV1.VALUE_contagious,
            ])
        ).toStrictEqual({
            [FixedCalendarPeriodV1.VALUE_contagious]: expectedContagiousRange,
            [FixedCalendarPeriodV1.VALUE_source]: expectedSourceRange,
        });
    });

    it('should return visit ranges for given context visit dates', () => {
        const store = useCalendarStore();
        expect(store.getContextVisitRanges([givenStartDate, givenEndDate])).toStrictEqual(expectedVisitRanges);
    });
});
