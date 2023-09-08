import { createLocalVue, shallowMount } from '@vue/test-utils';
import ComplianceIndexCases from './ComplianceIndexCases.vue';
import BootstrapVue from 'bootstrap-vue';
import FiltersPlugin from '@/plugins/filters';

describe('ComplianceIndexCases.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(FiltersPlugin);

    const setWrapper = (props?: object) =>
        shallowMount(ComplianceIndexCases, {
            localVue,
            propsData: props,
        });

    it('renders list of index cases', () => {
        const props = {
            indexCases: [
                {
                    uuid: '0001',
                    number: '0000001',
                    dateOfSymptomOnset: '2021-10-20',
                },
                {
                    uuid: '0002',
                    number: '0000002',
                    dateOfSymptomOnset: '2021-10-21',
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows).toHaveLength(2);

        expect(tableRows.at(0).html()).toContain('20-10-2021');
        expect(tableRows.at(0).html()).toContain('0000001');

        expect(tableRows.at(1).html()).toContain('21-10-2021');
        expect(tableRows.at(1).html()).toContain('0000002');
    });

    it('should emit "navigate" on view more arrow click', async () => {
        const props = {
            indexCases: [
                {
                    uuid: '0001',
                    number: '0000001',
                    dateOfSymptomOnset: '2021-10-20',
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.emitted().navigate).toBeFalsy();

        await wrapper.find('BButton-stub[tag="button"]').trigger('click');

        expect(wrapper.emitted().navigate).toBeTruthy();
        // @ts-ignore:next-line
        expect(wrapper.emitted().navigate[0]).toEqual(['0001']);
    });
});
