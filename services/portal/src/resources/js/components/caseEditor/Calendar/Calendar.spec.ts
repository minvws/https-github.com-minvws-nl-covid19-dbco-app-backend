import { createLocalVue, shallowMount } from '@vue/test-utils';
import Calendar from './Calendar.vue';
import BootstrapVue from 'bootstrap-vue';
import eachDayOfInterval from 'date-fns/eachDayOfInterval';
import { CalendarPeriodColorV1, FixedCalendarPeriodV1 } from '@dbco/enum';

describe('Calendar.vue', () => {
    const dateFnsFormatMock = vi.fn((value) => value.getTime());
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);

    const setWrapper = (props?: object) =>
        shallowMount(Calendar, {
            localVue,
            propsData: props,
            stubs: {},
            mocks: {
                $filters: {
                    dateFnsFormat: dateFnsFormatMock,
                },
            },
        });

    beforeEach(() => {
        dateFnsFormatMock.mockClear();
    });

    it('should render a calendar with dates and ranges', () => {
        const props = {
            ranges: [
                {
                    id: 'test_period',
                    type: 'period',
                    startDate: new Date(2020, 9, 21),
                    endDate: new Date(2020, 10, 4),
                    label: 'Testing period',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
                {
                    id: 'test_day',
                    type: 'point',
                    startDate: new Date(2020, 10, 1),
                    endDate: new Date(2020, 10, 1),
                    label: 'Testing day',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
            ],
        };

        const wrapper = setWrapper(props);

        const daysToRender = eachDayOfInterval({ start: new Date(2020, 9, 19), end: new Date(2020, 10, 8) });
        const testPeriodDaysToRender = eachDayOfInterval({ start: new Date(2020, 9, 21), end: new Date(2020, 10, 4) });

        expect(daysToRender).toHaveLength(21); // 3 full weeks
        expect(dateFnsFormatMock).toHaveBeenCalledTimes(23); // 21 weekdays + 2 month labels
        expect(dateFnsFormatMock).toHaveBeenCalledWith(new Date(2020, 9, 19), 'MMM'); // first month label
        expect(dateFnsFormatMock).toHaveBeenCalledWith(new Date(2020, 10, 1), 'MMM'); // second month label
        daysToRender.forEach((day) => expect(dateFnsFormatMock).toHaveBeenCalledWith(day, 'd')); // every day number
        expect(wrapper.findAll('[data-id="test_period"]')).toHaveLength(15); // the testing period days
        expect(wrapper.findAll('[data-id="test_day"]')).toHaveLength(1); // the testing day

        // Assert the actual date values
        testPeriodDaysToRender.forEach((day, index) =>
            expect(wrapper.findAll(`[data-id="test_period"] span:last-of-type`).at(index).text()).toBe(
                day.getTime().toString()
            )
        );
        expect(wrapper.find(`[data-id="test_day"] span:last-of-type`).text()).toBe(
            new Date(2020, 10, 1).getTime().toString()
        );
    });

    it('should render an overlap slice when source and infectious ranges have an overlap', () => {
        const props = {
            ranges: [
                {
                    id: 'source',
                    type: 'period',
                    key: FixedCalendarPeriodV1.VALUE_source,
                    startDate: new Date(2021, 10, 22),
                    endDate: new Date(2021, 11, 3),
                    label: 'Bronperiode',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
                {
                    id: 'infectious',
                    type: 'period',
                    key: FixedCalendarPeriodV1.VALUE_contagious,
                    startDate: new Date(2021, 11, 3),
                    endDate: new Date(2021, 11, 12),
                    label: 'Besmettelijke periode',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.findAll('[data-id="source"]')).toHaveLength(11);
        expect(wrapper.findAll('[data-id="infectious"]')).toHaveLength(9);
        expect(wrapper.findAll('[data-id="source,infectious"]')).toHaveLength(1);
        expect(wrapper.findAll('.is-overlap')).toHaveLength(1);
    });

    it('should not render an overlap slice when source and infectious ranges dont have an overlap', () => {
        const props = {
            ranges: [
                {
                    id: 'source',
                    type: 'period',
                    key: FixedCalendarPeriodV1.VALUE_source,
                    startDate: new Date(2021, 10, 22),
                    endDate: new Date(2021, 11, 3),
                    label: 'Bronperiode',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
                {
                    id: 'infectious',
                    type: 'period',
                    key: FixedCalendarPeriodV1.VALUE_contagious,
                    startDate: new Date(2021, 11, 4),
                    endDate: new Date(2021, 11, 13),
                    label: 'Besmettelijke periode',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.findAll('[data-id="source"]')).toHaveLength(12);
        expect(wrapper.findAll('[data-id="infectious"]')).toHaveLength(10);
        expect(wrapper.findAll('.is-overlap')).toHaveLength(0);
    });

    it('should render the legend', () => {
        const props = {
            showLegend: true,
            ranges: [
                {
                    id: 'test_period',
                    type: 'period',
                    startDate: new Date(2020, 9, 21),
                    endDate: new Date(2020, 10, 4),
                    label: 'Testing period',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
            ],
        };

        const wrapper = setWrapper(props);
        const legendItems = wrapper.findAll('.legend');
        // Vuejs renders hex in rgb so 254, 233, 234 are the rgb values of CalendarPeriodColorV1.VALUE_light_red.
        expect(legendItems.at(0).attributes().style).toContain('rgb(254, 233, 234)');
        expect(wrapper.text()).toContain('Testing period');
    });

    it('should emit onToggleDay event when date selected', async () => {
        const props = {
            editable: true,
            ranges: [
                {
                    id: 'test_period',
                    type: 'period',
                    startDate: new Date(2020, 9, 21),
                    endDate: new Date(2020, 10, 4),
                    label: 'Testing period',
                    color: CalendarPeriodColorV1.VALUE_light_red,
                },
            ],
        };

        const wrapper = setWrapper(props);

        await wrapper.find('.day-item.in-period').trigger('click');
        expect(wrapper.emitted('onToggleDay')).toStrictEqual([[new Date('2020-10-21')]]);
    });
});
