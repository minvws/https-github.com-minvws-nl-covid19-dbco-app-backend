import { setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import type { PiniaStateTree } from '@/store/storeType';
import { useAppStore } from '@/store/app/appStore';
import { shallowMount } from '@vue/test-utils';
import DbcoErrorAlert from './DbcoErrorAlert.vue';
import type { VueConstructor } from 'vue';

const REQUEST_ERROR_MODAL = 'error-modal';
const PERMISSION_ERROR_MODAL = 'permission-error-modal';

describe('DbcoErrorAlert.vue', () => {
    const shallowMountWithAppState = setupTest((localVue: VueConstructor, appState: Partial<PiniaStateTree['app']>) => {
        return shallowMount(DbcoErrorAlert, {
            localVue,
            pinia: createTestingPinia({
                stubActions: false,
                initialState: {
                    app: appState,
                },
            }),
        });
    });

    it('should load the component', () => {
        const wrapper = shallowMountWithAppState({});
        expect(wrapper.exists()).toBe(true);
    });

    it('should show modal if app/hasError is true', () => {
        const wrapper = shallowMountWithAppState({
            hasError: true,
        });

        expect(wrapper.findByTestId(REQUEST_ERROR_MODAL).props().visible).toBe(true);
    });

    it('should show modal if app/hasPermissionError is true', () => {
        const wrapper = shallowMountWithAppState({
            hasPermissionError: true,
        });

        expect(wrapper.findByTestId(PERMISSION_ERROR_MODAL).props().visible).toBe(true);
    });

    it('should not show modal if app/hasError is false', () => {
        const wrapper = shallowMountWithAppState({
            hasError: false,
        });

        expect(wrapper.findByTestId(REQUEST_ERROR_MODAL).props().visible).toBe(false);
    });

    it('should not show modal if app/hasPermissionError is false', () => {
        const wrapper = shallowMountWithAppState({
            hasPermissionError: false,
        });

        expect(wrapper.findByTestId(PERMISSION_ERROR_MODAL).props().visible).toBe(false);
    });

    it('should set app/hasError to false when modal emits "hide"-event', async () => {
        const wrapper = shallowMountWithAppState({
            hasError: true,
        });

        const appStore = useAppStore();
        const modal = wrapper.findByTestId(REQUEST_ERROR_MODAL);

        expect(modal.props().visible).toBe(true);

        await modal.vm.$emit('hide');

        expect(appStore.setHasError).toHaveBeenCalledWith(false);
        expect(modal.props().visible).toBe(false);
    });

    it('should set app/hasPermissionError to false when modal emits "hide"-event', async () => {
        const wrapper = shallowMountWithAppState({
            hasPermissionError: true,
        });

        const appStore = useAppStore();
        const modal = wrapper.findByTestId(PERMISSION_ERROR_MODAL);

        expect(modal.props().visible).toBe(true);

        await modal.vm.$emit('hide');

        expect(appStore.setHasPermissionError).toHaveBeenCalledWith(false);
        expect(modal.props().visible).toBe(false);
    });
});
