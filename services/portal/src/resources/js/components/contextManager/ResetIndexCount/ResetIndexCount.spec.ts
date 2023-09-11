import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import ResetIndexCount from './ResetIndexCount.vue';

import { createTestingPinia } from '@pinia/testing';
import i18n from '@/i18n/index';
import { useResetIndexCount } from '@/store/cluster/clusterStore';
import { Status } from '@/store/useStatusAction';

const createComponent = setupTest((localVue: VueConstructor, givenIndexCount = 4) => {
    return shallowMount(ResetIndexCount, {
        localVue,
        i18n,
        propsData: {
            indexCountSinceReset: givenIndexCount,
            placeUuid: fakerjs.string.uuid(),
        },
        pinia: createTestingPinia({
            stubActions: true,
        }),
    });
});

describe('ResetIndexCount.vue', () => {
    it('should render a button for resetting the index count', () => {
        // GIVEN its default state
        // WHEN the component is created
        const wrapper = createComponent();

        // THEN it should render a button for resetting the index count
        const button = wrapper.find('button');
        expect(button.text()).toBe(i18n.t('components.resetIndexCount.button'));
    });

    it('should disable the button for a indexCount of zero', () => {
        // GIVEN a indexCount of zero
        // WHEN the component is created
        const wrapper = createComponent(0);

        // THEN it should disable the button for resetting the index count
        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
    });

    it('should disable the button when the resetStatus value in the store is pending', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // WHEN the resetStatus in the store is pending
        const store = useResetIndexCount();
        store.resetStatus = { status: Status.pending };
        await flushCallStack();

        // THEN it should disable the button for resetting the index count
        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
    });

    it('should dispatch the "reset" Store action when the button is clicked', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        const store = useResetIndexCount();

        // WHEN the reset button is clicked
        const button = wrapper.find('button');
        await button.trigger('click');

        // THEN it should dispatch the "reset" Store action
        expect(store.reset).toHaveBeenCalledWith(wrapper.vm.$props.placeUuid);
    });

    it('should emit "reset" when the reset action in the store resolves', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();
        // AND the button was pressed
        const button = wrapper.find('button');
        await button.trigger('click');

        const store = useResetIndexCount();

        // WHEN the reset action resolves
        store.resetStatus = { status: Status.resolved, result: undefined };
        await flushCallStack();

        // THEN it should emit "reset"
        expect(wrapper.emitted().reset).toBeDefined();
    });

    it('should restore resetStatus value in store to idle', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();
        // AND the button was pressed
        const button = wrapper.find('button');
        await button.trigger('click');

        const store = useResetIndexCount();

        // WHEN the reset action resolves
        store.resetStatus = { status: Status.resolved, result: undefined };
        await flushCallStack();

        // THEN the store resetStatus value should have been restored to idle
        expect(store.resetStatus.status).toBe(Status.idle);
    });
});
