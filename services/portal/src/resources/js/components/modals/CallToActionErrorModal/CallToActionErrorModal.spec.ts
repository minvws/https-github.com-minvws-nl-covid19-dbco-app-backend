import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import { useChoreStore } from '@/store/chore/choreStore';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';

import i18n from '@/i18n/index';

import CallToActionErrorModal from '@/components/modals/CallToActionErrorModal/CallToActionErrorModal.vue';
import { fakeCallToAction } from '@/utils/__fakes__/callToAction';

const show = vi.fn();

const createComponent = setupTest((localVue: VueConstructor, givenStoreState: object = {}, props?: object) => {
    const BModal = {
        template: '<div />',
        methods: { show },
    };
    return shallowMount<CallToActionErrorModal>(CallToActionErrorModal, {
        localVue,
        i18n,
        propsData: props,
        pinia: createTestingPinia({
            initialState: {
                chore: givenStoreState,
            },
            stubActions: false,
        }),
        stubs: { BModal },
    });
});

describe('CallToActionErrorModal.vue', () => {
    it('should not render a modal when created', () => {
        // ARRANGE
        createComponent();

        // ASSERT
        expect(show).not.toHaveBeenCalled();
    });

    it('should render a modal when a backend error is committed to store', async () => {
        // ARRANGE
        const wrapper = createComponent();

        const choreStore = useChoreStore();
        choreStore.setBackendError({ message: fakerjs.lorem.words(), status: 404 });
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(show).toHaveBeenCalled();
    });

    it('should clear backend error when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent({
            selected: fakeCallToAction,
            backendError: { message: fakerjs.lorem.words(), status: 404 },
        });

        await wrapper.vm.$nextTick();
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hidden');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.vm.$pinia.state.value.chore.backendError).toBeNull();
    });

    it('should reset table when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent({
            selected: fakeCallToAction,
            backendError: { message: fakerjs.lorem.words(), status: 404 },
        });
        const spyAction = vi.spyOn(useCallToActionStore(), 'resetTable');

        await wrapper.vm.$nextTick();
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hidden');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(spyAction).toHaveBeenCalled();
    });
});
