import { defineStore } from 'pinia';
import { StoreType } from '../storeType';
import store from '@/store';
import { CalendarPointColorV1 } from '@dbco/enum';
import type { CalendarViewV1, FixedCalendarPeriodV1 } from '@dbco/enum';
import { parseDate } from '@/utils/date';
import _, { isEmpty } from 'lodash';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';

type CalendarStoreState = {
    calendarData: CalendarDateRange[];
    calendarViews: Partial<{ [key in CalendarViewV1]: string[] }>;
    dateOfLastExposure: string | null;
};

export const useCalendarStore = defineStore('calendar', {
    getters: {
        getCalendarDataByView: (state: CalendarStoreState) => {
            return (view: CalendarViewV1) => {
                const calendarView = state.calendarViews[view];

                if (!calendarView) return [];

                const data = calendarView
                    .map((id) => {
                        return state.calendarData.find((dataObj: CalendarDateRange) => dataObj.id === id);
                    })
                    .filter((value) => value !== undefined);

                return data as CalendarDateRange[];
            };
        },
        getCalendarDateItemsByKey: (state: CalendarStoreState) => {
            return (keys: FixedCalendarPeriodV1[]) => {
                if (isEmpty(state.calendarData)) return [];

                return _.keyBy(
                    state.calendarData.filter(
                        (dataObj: CalendarDateRange) => dataObj.key && keys.includes(dataObj.key)
                    ),
                    'key'
                );
            };
        },
        getLastContactDateRange(): CalendarDateRange | null {
            if (!this.dateOfLastExposure) return null;

            const lastExposureDay = parseDate(this.dateOfLastExposure, 'yyyy-MM-dd');
            return {
                id: 'lastExposureDay',
                type: 'point',
                startDate: lastExposureDay,
                endDate: lastExposureDay,
                label: 'Contactdatum(s)',
                color: CalendarPointColorV1.VALUE_purple,
            };
        },
        getContextVisitRanges(): (visitDates: Date[]) => CalendarDateRange[] {
            return (visitDates: Date[]) => {
                const contextVisitRanges: CalendarDateRange[] = [];

                visitDates.forEach((visitDate) => {
                    contextVisitRanges.push({
                        id: 'contextVisistedRanges',
                        type: 'point',
                        startDate: visitDate,
                        endDate: visitDate,
                        label: 'Contactdatum(s)',
                        color: CalendarPointColorV1.VALUE_purple,
                    });
                });
                return contextVisitRanges;
            };
        },
        calendarData(): CalendarStoreState['calendarData'] {
            return store.getters[`${StoreType.INDEX}/calendarData`] as CalendarStoreState['calendarData'];
        },
        calendarViews(): CalendarStoreState['calendarViews'] {
            return store.getters[`${StoreType.INDEX}/calendarViews`] as CalendarStoreState['calendarViews'];
        },
        dateOfLastExposure() {
            return store.getters[`${StoreType.TASK}/dateOfLastExposure`] as string | null;
        },
    },
});
