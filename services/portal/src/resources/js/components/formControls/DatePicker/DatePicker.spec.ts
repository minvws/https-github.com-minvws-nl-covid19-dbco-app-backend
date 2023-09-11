import { createLocalVue, shallowMount } from '@vue/test-utils';
import DatePicker from './DatePicker.vue';
import BootstrapVue, { BFormInput } from 'bootstrap-vue';
import type { UntypedWrapper } from '@/utils/test';
import { createContainer } from '@/utils/test';
import dateFnsFormat from 'date-fns/format';
import { nl } from 'date-fns/locale';

describe('DatePicker.vue', () => {
    const dateFnsFormatMock = vi.fn((value) => value.getTime());
    const dateFormatLongMock = vi.fn((value) => dateFnsFormat(new Date(value), 'EEEE d MMM', { locale: nl }));
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.directive('click-outside', vi.fn());

    const setWrapper = (props?: object, data: object = {}, BFormInputStubbed = false) =>
        shallowMount(DatePicker, {
            localVue,
            data: () => data,
            propsData: props,
            stubs: { BFormInput: BFormInputStubbed ? true : BFormInput },
            attachTo: createContainer(), // supresses [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.
            mocks: {
                $filters: {
                    dateFnsFormat: dateFnsFormatMock,
                    dateFormatLong: dateFormatLongMock,
                },
            },
        }) as UntypedWrapper;

    beforeEach(() => {
        dateFnsFormatMock.mockClear();
    });

    it('should render a multi select datepicker', () => {
        const props = {};

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const input = wrapper.findComponent({ name: 'BFormInput' });
        expect(input.exists()).toBe(true);
        expect(input.attributes('placeholder')).toBe('Kies datum(s)');

        const warningIcon = wrapper.findComponent({ name: 'warningicon-stub' });
        expect(warningIcon.exists()).toBe(false);
    });

    it('should render a single select datepicker', () => {
        const props = { singleSelection: true };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const input = wrapper.findComponent({ name: 'BFormInput' });
        expect(input.exists()).toBe(true);
        expect(input.attributes('placeholder')).toBe('Kies datum');
    });

    it('should show warning if provided', () => {
        const props = { inputWarning: 'this is a warning' };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const warningIcon = wrapper.get('IconWarningSvg-stub');

        expect(warningIcon.exists()).toBe(true);
    });

    it('should show summary of selection in input', async () => {
        const props = { value: ['2022-01-19', '2022-01-20'] };

        const wrapper = setWrapper(props);
        await wrapper.vm.$nextTick();

        const input = wrapper.findComponent({ name: 'BFormInput' });
        const inputElement = input.element as HTMLInputElement;
        expect(input.exists()).toBe(true);
        expect(inputElement.value).toBe('19 - 20 jan.');
    });

    it('should show summary with day of week in input for single selection', () => {
        const props = { value: ['2022-01-19'], singleSelection: true };

        const wrapper = setWrapper(props);

        const input = wrapper.findComponent({ name: 'BFormInput' });
        const inputElement = input.element as HTMLInputElement;
        expect(input.exists()).toBe(true);
        expect(inputElement.value).toBe('woensdag 19 jan.');
    });

    it('should open the calendar on click', async () => {
        const props = {};

        const spyInputClick = vi.spyOn((DatePicker as any).methods, 'inputClick');

        const wrapper = setWrapper(props);

        const input = wrapper.findComponent({ name: 'BFormInput' });
        await input.trigger('keydown.enter');

        expect(spyInputClick).toHaveBeenCalledTimes(1);
        expect(wrapper.findComponent({ name: 'Calendar' }).exists()).toBe(true);
    });

    it('should add day to selected days on toggle a day', async () => {
        const props = { value: ['2022-01-19', '2022-01-20'] };
        const data = { isOpen: true };

        const wrapper = setWrapper(props, data);
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-19', '2022-01-20']);

        const calendar = wrapper.findComponent({ name: 'Calendar' });
        calendar.vm.$emit('onToggleDay', new Date('2022-01-21'));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-19', '2022-01-20', '2022-01-21']);
        expect(wrapper.emitted().input).toBeTruthy();
        // modal should also remain open
        expect(wrapper.vm.isOpen).toBe(true);
        expect(calendar.exists()).toBe(true);
    });

    it('should remove day from selected days on toggle a day, if the same day', async () => {
        const props = { value: ['2022-01-19', '2022-01-20'] };
        const data = { isOpen: true };

        const wrapper = setWrapper(props, data);
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-19', '2022-01-20']);

        const calendar = wrapper.findComponent({ name: 'Calendar' });
        calendar.vm.$emit('onToggleDay', new Date('2022-01-19'));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-20']);
        expect(wrapper.emitted().input).toBeTruthy();
    });

    it('should replace selected day on toggle a day, if single selection', async () => {
        const props = { value: ['2022-01-19'], singleSelection: true };
        const data = { isOpen: true };

        const wrapper = setWrapper(props, data);
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-19']);

        const calendar = wrapper.findComponent({ name: 'Calendar' });
        calendar.vm.$emit('onToggleDay', new Date('2022-01-20'));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.selectedDays).toStrictEqual(['2022-01-20']);
        expect(wrapper.emitted().input).toBeTruthy();
        // modal should also auto close
        expect(wrapper.vm.isOpen).toBe(false);
        expect(calendar.exists()).toBe(false);
    });

    it('should disable "input-placeholder" if disabled prop is true', async () => {
        const props = { value: ['2022-01-19'], disabled: true };

        const wrapper = setWrapper(props, {}, true);
        await wrapper.vm.$nextTick();

        expect(wrapper.find("[data-testid='input-placeholder']").attributes().disabled).toBe('true');
    });

    it('should disable "input-placeholder" if disabled prop is false', async () => {
        const props = { value: ['2022-01-19'], disabled: false };

        const wrapper = setWrapper(props, {}, true);
        await wrapper.vm.$nextTick();

        expect(wrapper.find("[data-testid='input-placeholder']").attributes().disabled).toBe(undefined);
    });
});
