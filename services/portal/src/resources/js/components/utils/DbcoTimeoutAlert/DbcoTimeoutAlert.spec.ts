import type { PiniaStateTree } from '@/store/storeType';
import { flushCallStack, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';

import { enableMockServer } from '@/integration-specs/utils/msw-init';
import { shallowMount } from '@vue/test-utils';
import axios from 'axios';
import DbcoTimeoutAlert from './DbcoTimeoutAlert.vue';
import { rest } from 'msw';
import type { VueConstructor } from 'vue';

const OFFLINE_ERROR = 'offline-error-alert';
const OFFLINE_ERROR_MODAL = 'offline-error-modal';
const MORE_INFO_BUTTON = 'button';

const mockServer = enableMockServer();

describe('DbcoTimeoutAlert.vue', () => {
    const shallowMountWithAppState = setupTest(
        (localVue: VueConstructor, appState: Partial<PiniaStateTree['app']> = {}) => {
            return shallowMount(DbcoTimeoutAlert, {
                localVue,
                pinia: createTestingPinia({
                    stubActions: false,
                    initialState: {
                        app: appState,
                    },
                }),
            });
        }
    );

    beforeEach(() => {
        vi.clearAllTimers();
        vi.clearAllMocks();
        vi.useFakeTimers();
    });

    afterAll(() => {
        vi.useRealTimers();
    });

    it('should load the component', () => {
        const wrapper = shallowMountWithAppState({});
        expect(wrapper.exists()).toBe(true);
    });

    it('should show a message if app/isOffline is true', () => {
        const wrapper = shallowMountWithAppState({
            isOffline: true,
        });

        expect(wrapper.findByTestId(OFFLINE_ERROR).isVisible()).toBe(true);
    });

    it('should not show modal if app/isOffline is false', () => {
        const wrapper = shallowMountWithAppState({
            isOffline: false,
        });

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);
    });

    it('shows more information if the users clicks "more info" and is able to close it', async () => {
        const wrapper = shallowMountWithAppState({
            isOffline: true,
        });

        const moreInfoModal = wrapper.findByTestId(OFFLINE_ERROR_MODAL);

        expect(wrapper.findByTestId(OFFLINE_ERROR).isVisible()).toBe(true);
        expect(moreInfoModal.props().visible).toBe(false);

        await wrapper.find(MORE_INFO_BUTTON).trigger('click');

        expect(moreInfoModal.props().visible).toBe(true);

        await moreInfoModal.vm.$emit('ok');

        expect(moreInfoModal.props().visible).toBe(false);
    });

    it('should respond to online / offline events', async () => {
        const wrapper = shallowMountWithAppState();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);

        window.dispatchEvent(new Event('offline'));
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(true);

        window.dispatchEvent(new Event('online'));
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);
    });

    it('should keep the message until the ping is succesful', async () => {
        const wrapper = shallowMountWithAppState();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);

        window.dispatchEvent(new Event('offline'));
        await wrapper.vm.$nextTick();
        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(true);

        vi.runOnlyPendingTimers();
        await wrapper.vm.$nextTick();
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(true);
    });

    it('should hide message when ping is succesful', async () => {
        mockServer.use(
            rest.get('/ping', (_req, res, ctx) => {
                return res.once(ctx.status(200), ctx.body('PONG'));
            })
        );

        const wrapper = shallowMountWithAppState();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);

        window.dispatchEvent(new Event('offline'));
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(true);
        vi.runOnlyPendingTimers();

        vi.useRealTimers();
        await flushCallStack();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);
    });

    it('should cleanup when component is unmounted', async () => {
        vi.spyOn(global, 'clearTimeout');
        vi.spyOn(window, 'removeEventListener');

        const request = axios.CancelToken.source();
        vi.spyOn(request, 'cancel').mockImplementationOnce(() => undefined);
        vi.spyOn(axios.CancelToken, 'source').mockReturnValue(request);

        const wrapper = shallowMountWithAppState();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(false);

        window.dispatchEvent(new Event('offline'));
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId(OFFLINE_ERROR).exists()).toBe(true);
        vi.runOnlyPendingTimers();

        wrapper.destroy();

        expect(request.cancel).toHaveBeenCalled();
        expect(clearTimeout).toHaveBeenCalled();
        expect(window.removeEventListener).toHaveBeenCalledWith('online', expect.any(Function));
        expect(window.removeEventListener).toHaveBeenCalledWith('offline', expect.any(Function));
    });
});
