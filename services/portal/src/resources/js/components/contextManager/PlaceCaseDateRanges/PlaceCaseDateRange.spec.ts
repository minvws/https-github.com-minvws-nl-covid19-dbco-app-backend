import type { VueConstructor } from 'vue';
import { setupTest } from '@/utils/test';

import PlaceCaseDateRanges from './PlaceCaseDateRanges.vue';
import { fakePlaceCase } from '@/utils/__fakes__/place';
import { YesNoUnknownV1 } from '@dbco/enum';
import type { PlaceCasesResponse } from '@dbco/portal-api/place.dto';
import { render } from '@testing-library/vue';
import { fakeCalendarDateRange } from '@/utils/__fakes__/calendarDateRange';

const createComponent = setupTest((localVue: VueConstructor, givenPlaceCase: Partial<PlaceCasesResponse>) => {
    return render<PlaceCaseDateRanges>(PlaceCaseDateRanges, {
        localVue,
        propsData: { placeCase: fakePlaceCase(givenPlaceCase) },
    });
});

describe('PlaceCasesDateRanges.vue', () => {
    return it.each([
        {
            hasSymptoms: YesNoUnknownV1.VALUE_yes,
            dateOfSymptomOnset: '2022-11-23',
            dateOfTest: '2022-11-23',
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'circle-blue',
                    label: 'source dates',
                }),
                fakeCalendarDateRange({ startDate: '2022-11-21', icon: 'range-overlap', label: 'overlap date' }),
                fakeCalendarDateRange({
                    startDate: '2022-11-22',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-23',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
            ],
            expected: {
                'source dates': '20 nov',
                'overlap date': '21 nov',
                'infectious dates': '22 - 24 nov',
                'unknown dates': undefined,
            },
        },
        {
            hasSymptoms: YesNoUnknownV1.VALUE_yes,
            dateOfSymptomOnset: '2022-11-23',
            dateOfTest: '2022-11-23',
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'circle-blue',
                    label: 'source dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
            ],
            expected: {
                'source dates': '20 nov',
                'overlap date': undefined,
                'infectious dates': '24 nov',
                'unknown dates': undefined,
            },
        },
        {
            hasSymptoms: YesNoUnknownV1.VALUE_no,
            dateOfSymptomOnset: '2022-11-23',
            dateOfTest: '2022-11-23',
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'circle-blue',
                    label: 'source dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-21',
                    icon: 'circle-blue',
                    label: 'source dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-22',
                    icon: 'circle-blue',
                    label: 'source dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-23',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'square-red',
                    label: 'infectious dates',
                }),
            ],
            expected: {
                'source dates': '20 - 22 nov',
                'infectious dates': '23 - 24 nov',
                'overlap date': undefined,
                'unknown dates': undefined,
            },
        },
        {
            hasSymptoms: YesNoUnknownV1.VALUE_unknown,
            dateOfSymptomOnset: '2022-11-23',
            dateOfTest: '2022-11-23',
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-21',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-22',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-23',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
            ],
            expected: {
                'overlap date': undefined,
                'source dates': undefined,
                'infectious dates': undefined,
                'unknown dates': '20 - 24 nov',
            },
        },
        {
            hasSymptoms: YesNoUnknownV1.VALUE_yes,
            dateOfSymptomOnset: null,
            dateOfTest: '2022-11-23',
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-21',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-22',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-23',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
            ],
            expected: {
                'overlap date': undefined,
                'source dates': undefined,
                'infectious dates': undefined,
                'unknown dates': '20 - 24 nov',
            },
        },
        {
            hasSymptoms: YesNoUnknownV1.VALUE_no,
            dateOfSymptomOnset: null,
            dateOfTest: null,
            moments: [
                fakeCalendarDateRange({
                    startDate: '2022-11-20',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-21',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-22',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-23',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
                fakeCalendarDateRange({
                    startDate: '2022-11-24',
                    icon: 'diamond-grey',
                    label: 'unknown dates',
                }),
            ],
            expected: {
                'overlap date': undefined,
                'source dates': undefined,
                'infectious dates': undefined,
                'unknown dates': '20 - 24 nov',
            },
        },
    ])(
        'should correctly render the case date ranges',
        async ({ hasSymptoms, dateOfSymptomOnset, dateOfTest, moments, expected }) => {
            // GIVEN the component renders with table data
            const wrapper = createComponent({ symptoms: { hasSymptoms }, dateOfSymptomOnset, dateOfTest, moments });

            for (const icon of Object.keys(expected)) {
                if (expected[icon as keyof typeof expected]) {
                    expect((await wrapper.findByRole('img', { name: icon })).parentElement?.textContent).toContain(
                        expected[icon as keyof typeof expected]
                    );
                } else {
                    expect(wrapper.queryAllByRole('img', { name: icon }).length).toBe(0);
                }
            }
        }
    );
});
