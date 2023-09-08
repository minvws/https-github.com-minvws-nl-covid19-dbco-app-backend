import { createLocalVue, shallowMount } from '@vue/test-utils';
import CaseUpdateInfoBar from './CaseUpdateInfoBar.vue';
import Vuex, { Store } from 'vuex';
import BootstrapVue from 'bootstrap-vue';
import indexStore from '@/store/index/indexStore';
import { caseUpdateApi } from '@dbco/portal-api';
import { flushCallStack } from '@/utils/test';

vi.mock('@dbco/portal-api/client/caseUpdate.api', () => ({
    listCaseUpdates: vi.fn(() =>
        Promise.resolve({
            total: 2,
            items: [
                {
                    uuid: '1234',
                    receivedAt: '2022-01-01T00:00:00.000Z',
                },
                {
                    uuid: '5678',
                    receivedAt: '2022-01-01T00:00:00.000Z',
                },
            ],
        })
    ),
}));

describe('CaseUpdateInfoBar.vue', () => {
    const dateFnsFormatMock = vi.fn();

    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const setWrapper = (data: object = {}) =>
        shallowMount(CaseUpdateInfoBar, {
            localVue,
            data: () => data,
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
        });
    it('should show the component if data.caseUpdate is set', async () => {
        // API call is mocked and will be executed on created
        const wrapper = setWrapper();
        await flushCallStack();
        expect(wrapper.isVisible()).toBe(true);
    });

    it('should not show the component if data.caseUpdate is not set', async () => {
        vi.spyOn(caseUpdateApi, 'listCaseUpdates').mockImplementationOnce(() =>
            Promise.resolve({
                total: 0,
                items: [],
            })
        );

        const wrapper = setWrapper();
        await wrapper.vm.$nextTick();

        expect(wrapper.isVisible()).toBe(false);
    });

    it('should show receivedAt date and "Bekijken" button', async () => {
        const wrapper = setWrapper();
        await flushCallStack();

        expect(dateFnsFormatMock).toHaveBeenCalledWith('2022-01-01T00:00:00.000Z', "dd MMM 'om' H:mm");
        expect(wrapper.findComponent({ name: 'BButton' }).exists()).toBe(true);
    });

    it('should not show the modal by default', () => {
        const wrapper = setWrapper();

        expect(wrapper.findComponent({ name: 'CaseUpdateModal' }).exists()).toBe(false);
    });

    it('should show the modal when clicking the "Bekijk" button', async () => {
        const wrapper = setWrapper();
        await flushCallStack();
        await wrapper.findComponent({ name: 'BButton' }).trigger('click');

        expect(wrapper.findComponent({ name: 'CaseUpdateModal' }).exists()).toBe(true);
    });

    it('should hide the modal after CaseUpdateModal emits "hide" event', async () => {
        const data = {
            showModal: true,
        };
        const wrapper = setWrapper(data);

        await flushCallStack();

        await wrapper.findComponent({ name: 'CaseUpdateModal' }).vm.$emit('hide');

        expect(wrapper.findComponent({ name: 'CaseUpdateModal' }).exists()).toBe(false);
    });

    it('should hide the component afterCaseUpdateModal emits "submitted" event', async () => {
        const data = {
            showModal: true,
        };
        const wrapper = setWrapper(data);
        await flushCallStack();

        await wrapper.findComponent({ name: 'CaseUpdateModal' }).vm.$emit('submitted');

        expect(wrapper.isVisible()).toBe(false);
    });
});
