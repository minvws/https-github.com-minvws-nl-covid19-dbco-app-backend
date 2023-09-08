import { createLocalVue, shallowMount } from '@vue/test-utils';
import CaseUpdateModal from './CaseUpdateModal.vue';
import Vuex, { Store } from 'vuex';
import BootstrapVue from 'bootstrap-vue';
import type { CaseUpdateItem } from '@dbco/portal-api/caseUpdate.dto';
import { caseUpdateApi } from '@dbco/portal-api';
import indexStore from '@/store/index/indexStore';
import type { UntypedWrapper } from '@/utils/test';
import { flushCallStack } from '@/utils/test';
import TypingHelpers from '@/plugins/typings';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getFragments: vi.fn(() => Promise.resolve({})),
    getMeta: vi.fn(() => Promise.resolve({})),
}));

vi.mock('@dbco/portal-api/client/caseUpdate.api', () => ({
    getCaseUpdate: vi.fn(
        (): Promise<CaseUpdateItem> =>
            Promise.resolve({
                uuid: '1234',
                receivedAt: '2022-01-01T00:00:00.000Z',
                source: 'source',
                fragments: [
                    {
                        name: 'test name',
                        label: 'test label',
                        fields: [
                            {
                                id: '123',
                                name: 'field name 1',
                                label: 'field label 1',
                                oldValue: 'old value 1',
                                newValue: 'new value 1',
                                oldDisplayValue: 'a',
                                newDisplayValue: 'b',
                            },
                            {
                                id: '456',
                                name: 'field name 2',
                                label: 'field label 2',
                                oldValue: 'old value 2',
                                newValue: 'new value 2',
                                oldDisplayValue: 'c',
                                newDisplayValue: 'd',
                            },
                        ],
                    },
                ],
                contacts: [
                    {
                        label: 'contact',
                        fragments: [
                            {
                                name: 'contact name',
                                label: 'contact label',
                                fields: [
                                    {
                                        id: '789',
                                        name: 'field name 1',
                                        label: 'field label 1',
                                        oldValue: 'old value 1',
                                        newValue: 'new value 1',
                                        oldDisplayValue: 'a',
                                        newDisplayValue: 'b',
                                    },
                                ],
                            },
                        ],
                    },
                ],
            })
    ),
    applyCaseUpdate: vi.fn(() => Promise.resolve({})),
}));

describe('CaseUpdateModal.vue', () => {
    const dateFnsFormatMock = vi.fn();

    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(TypingHelpers);

    const setWrapper = () =>
        shallowMount(CaseUpdateModal, {
            localVue,
            propsData: {
                caseUpdateId: 'd7fbaba2-3a52-45bc-bfae-3f7be0ceaa57',
            },
            data: () => ({}),
            store: new Store({
                modules: {
                    index: {
                        ...indexStore,
                        state: {
                            ...indexStore.state,
                            uuid: '1234',
                        },
                    },
                },
            }),
            mocks: {
                $filters: {
                    dateFnsFormat: dateFnsFormatMock,
                },
            },
        }) as UntypedWrapper;

    it('should hide component if caseUpdate is falsy', () => {
        const wrapper = setWrapper();

        expect(wrapper.isVisible()).toBe(false);
    });

    it('should call caseUpdate and show component if data is available', async () => {
        const spyGetCaseUpdate = vi.spyOn(caseUpdateApi, 'getCaseUpdate');
        const wrapper = setWrapper();
        await flushCallStack();

        expect(wrapper.isVisible()).toBe(true);
        expect(spyGetCaseUpdate).toHaveBeenCalledTimes(1);
    });

    it('should show items', async () => {
        const wrapper = setWrapper();
        await flushCallStack();

        // Two TBody's are expected: one for the case and one for the contact
        const tBodies = wrapper.findAllComponents({ name: 'BTbody' });

        // Should be 3 rows: one for the item and two for the fields
        expect(tBodies.at(0).findAllComponents({ name: 'BTr' }).length).toBe(3);
        // Should be 2 rows: one for the item and one for the field
        expect(tBodies.at(1).findAllComponents({ name: 'BTr' }).length).toBe(2);
    });

    it('should select/unselect all items when pressing toggle all checkbox', async () => {
        const wrapper = setWrapper();
        await flushCallStack();

        // By default all items are selected
        expect(wrapper.vm.selected.length).toBe(3);

        // Two TBody's are expected: one for the case and one for the contact
        const checkAllBox = wrapper.findComponent({ name: 'BThead' }).findComponent({ name: 'BFormCheckbox' });
        checkAllBox.vm.$emit('change');

        // Now they should be unselected
        expect(wrapper.vm.selected.length).toBe(0);
    });

    it('should show receivedAt date', async () => {
        const wrapper = setWrapper();
        await wrapper.vm.$nextTick();

        // We cannot see the BModal template due to shallowMount
        // But we can detect the usage of the dateFnsFormat filter
        expect(dateFnsFormatMock).toHaveBeenCalledWith('2022-01-01T00:00:00.000Z', "dd MMM 'om' H:mm");
    });

    it("should update and reload the index data when pressing button 'Overnemen'", async () => {
        const wrapper = setWrapper();
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        await flushCallStack();

        const button = wrapper.findComponent({ name: 'BButton' });
        await button.trigger('click');
        await wrapper.vm.$nextTick();

        expect(caseUpdateApi.applyCaseUpdate).toHaveBeenCalledTimes(1);
        expect(spyOnDispatch).toHaveBeenCalledWith('index/LOAD', '1234');
    });
});
