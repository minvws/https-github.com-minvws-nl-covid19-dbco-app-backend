import { createLocalVue, shallowMount } from '@vue/test-utils';
import ComplianceContactCases from './ComplianceContactCases.vue';
import BootstrapVue from 'bootstrap-vue';
import { RelationshipV1, relationshipV1Options } from '@dbco/enum';

describe('ComplianceContactCases.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);

    const setWrapper = (props?: object) =>
        shallowMount(ComplianceContactCases, {
            localVue,
            propsData: props,
            mocks: {
                $filters: {
                    dateFormat: vi.fn((x) => x),
                    categoryFormat: vi.fn((x) => x),
                },
            },
        });

    it('renders list of contact cases', () => {
        const props = {
            contactCases: [
                {
                    uuid: '0001',
                    contactDate: '2021-10-20',
                    category: '2a',
                    index: {
                        number: '0000001',
                        relationship: RelationshipV1.VALUE_partner,
                    },
                },
                {
                    uuid: '0002',
                    contactDate: '2021-10-21',
                    category: '1',
                    index: {
                        number: '0000002',
                        relationship: RelationshipV1.VALUE_roommate,
                    },
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows).toHaveLength(2);

        expect(tableRows.at(0).html()).toContain('0000001');
        expect(tableRows.at(0).html()).toContain('2a');
        expect(tableRows.at(0).html()).toContain(relationshipV1Options.partner);

        expect(tableRows.at(1).html()).toContain('0000002');
        expect(tableRows.at(1).html()).toContain('1');
        expect(tableRows.at(1).html()).toContain(relationshipV1Options.roommate);
    });

    it('renders contact case with relationship', () => {
        const props = {
            contactCases: [
                {
                    uuid: '0001',
                    contactDate: '2021-10-20',
                    category: '2a',
                    index: {
                        number: '0000001',
                        relationship: RelationshipV1.VALUE_partner,
                    },
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows.at(0).html()).toContain('0000001');
        expect(tableRows.at(0).html()).toContain('2a');
        expect(tableRows.at(0).html()).toContain(relationshipV1Options.partner);
    });

    it('renders contact case without relationship', () => {
        const props = {
            contactCases: [
                {
                    uuid: '0001',
                    contactDate: '2021-10-20',
                    category: '2a',
                    index: {
                        number: '0000001',
                    },
                },
            ],
        };

        const wrapper = setWrapper(props);

        expect(wrapper.exists()).toBe(true);

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows.at(0).html()).toContain('0000001');
        expect(tableRows.at(0).html()).toContain('2a');
        expect(tableRows.at(0).html()).not.toContain(relationshipV1Options.partner);
    });

    it('should emit "navigate" on view more arrow click', async () => {
        const props = {
            contactCases: [
                {
                    uuid: '0001',
                    contactDate: '2021-10-20',
                    category: '2a',
                    index: {
                        number: '0000001',
                    },
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
