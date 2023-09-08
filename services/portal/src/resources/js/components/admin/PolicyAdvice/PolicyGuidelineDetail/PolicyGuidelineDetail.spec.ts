import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';
import PolicyGuidelineDetail from './PolicyGuidelineDetail.vue';
import { fakeCalendarItemConfig, fakePolicyGuideline } from '@/utils/__fakes__/admin';
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import { useRoute, useRouter } from '@/router/router';
import type { Mock } from 'vitest';
import { adminApi } from '@dbco/portal-api';
import { Spinner } from '@dbco/ui-library';
import { useEventBus } from '@/composables/useEventBus';

vi.mock('@/router/router');
vi.mock('@dbco/portal-api/client/admin.api', () => ({
    getPolicyGuideline: vi.fn(() => Promise.resolve(fakePolicyGuideline())),
    getCalendarItemConfigs: vi.fn(() => Promise.resolve([fakeCalendarItemConfig()])),
}));

const eventBus = useEventBus();
const givenGuideline = fakePolicyGuideline();
const { policyVersionUuid: versionUuid, uuid: policyGuidelineUuid } = givenGuideline;

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(PolicyGuidelineDetail, {
        localVue,
        propsData: props,
        stubs: { Backdrop: true, InfoBar: true, LastUpdated: true },
    });
});

describe('PolicyGuidelineDetail.vue', () => {
    it('should render full screen modal with the name of the current guideline in the title', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, policyGuidelineUuid } }));
        vi.spyOn(adminApi, 'getPolicyGuideline').mockImplementationOnce(() => Promise.resolve(givenGuideline));

        const wrapper = createComponent();
        await flushCallStack();

        const modal = wrapper.findComponent(FullScreenModal);

        expect(modal.props('path')).toContain(givenGuideline.name);
    });

    it('should call the vueRouter back method when the full screen modal is closed', async () => {
        const wrapper = createComponent();
        const modal = wrapper.findComponent(FullScreenModal);

        await modal.vm.$emit('onClose');
        await flushCallStack();

        expect(useRouter().back).toHaveBeenCalledTimes(1);
    });

    it('should show a spinner when the loading status is "pending"', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, policyGuidelineUuid } }));

        const wrapper = createComponent();
        wrapper.vm._setupState.loadPending = true;
        await flushCallStack();

        const spinner = wrapper.findComponent(Spinner);
        expect(spinner.isVisible()).toBe(true);
    });

    it('should show a message when loading status is not "pending" and no item is found', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, policyGuidelineUuid } }));
        vi.spyOn(adminApi, 'getPolicyGuideline').mockImplementationOnce(() => Promise.reject());

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.text()).toContain('Geen richtlijn gevonden');
    });

    it('should set guideline when the "policy-status-change" event is triggered', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, policyGuidelineUuid } }));
        const spyOnApi = vi
            .spyOn(adminApi, 'getPolicyGuideline')
            .mockImplementationOnce(() => Promise.resolve(givenGuideline));

        createComponent();
        await flushCallStack();

        eventBus.$emit('policy-status-change');

        expect(spyOnApi).toHaveBeenCalledTimes(2);
    });

    it('should clean up on unmount', () => {
        vi.spyOn(eventBus, '$off');
        const wrapper = createComponent();
        wrapper.destroy();
        expect(eventBus.$off).toHaveBeenCalledWith('policy-status-change', expect.any(Function));
    });
});
