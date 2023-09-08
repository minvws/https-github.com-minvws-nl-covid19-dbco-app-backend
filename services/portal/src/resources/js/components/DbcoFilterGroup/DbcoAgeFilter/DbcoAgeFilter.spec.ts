import DbcoAgeFilter from './DbcoAgeFilter.vue';
import { shallowMount } from '@vue/test-utils';
import { BDropdown, BInputGroup, BFormInput } from 'bootstrap-vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import i18n from '@/i18n';

const defaultProps = {
    ageLabel: 'Alle leeftijden',
};

const createComponent = setupTest((localVue: VueConstructor, props: object = defaultProps, data: object = {}) => {
    return shallowMount(DbcoAgeFilter, {
        localVue,
        i18n,
        propsData: { ...defaultProps, ...props },
        data: () => data,
        stubs: { BDropdown, BFormInput, BInputGroup },
    });
});

describe('DBCOAgeFilter', () => {
    it('should be visible and display all elements within the dropdown', () => {
        const wrapper = createComponent();
        const inputMinAge = wrapper.find('#ageMin');
        const inputMaxAge = wrapper.find('#ageMax');
        const resetFilterButton = wrapper.find('#resetFilterButton');
        const sendAgeFilterButton = wrapper.find('#sendAgeFilterButton');
        const dropdown = wrapper.findComponent({ name: 'BDropdown' });

        expect(dropdown.exists()).toBe(true);

        expect(inputMinAge.exists()).toBe(true);
        expect(inputMinAge.attributes('placeholder')).toBe('0');

        expect(inputMaxAge.exists()).toBe(true);
        expect(inputMaxAge.attributes('placeholder')).toBe('120');

        expect(resetFilterButton.exists()).toBe(true);
        expect(sendAgeFilterButton.exists()).toBe(true);
    });

    it('should reset inputs on resetFilterButton', async () => {
        const wrapper = createComponent();
        const inputMinAge = wrapper.find('#ageMin');
        const inputMaxAge = wrapper.find('#ageMax');
        const inputElementMinAge = inputMinAge.element as HTMLInputElement;
        const inputElementMaxAge = inputMaxAge.element as HTMLInputElement;
        const resetFilterButton = wrapper.find('#resetFilterButton');

        await inputMinAge.setValue('15');
        await inputMaxAge.setValue('66');

        expect(inputElementMinAge.value).toBe('15');
        expect(inputElementMaxAge.value).toBe('66');

        await resetFilterButton.trigger('click');

        expect(inputElementMinAge.value).toBe('0');
        expect(inputElementMaxAge.value).toBe('120');
    });

    it('should filter the age and display the filtered cases', async () => {
        const wrapper = createComponent();
        const spyDropdownHide = vi.spyOn(wrapper.vm.$refs.dropDownRef as BDropdown, 'hide');
        const sendAgeFilterButton = wrapper.find('#sendAgeFilterButton');

        expect(sendAgeFilterButton.exists()).toBe(true);

        await sendAgeFilterButton.trigger('click');

        expect(spyDropdownHide).toHaveBeenCalledTimes(1);
    });
});
