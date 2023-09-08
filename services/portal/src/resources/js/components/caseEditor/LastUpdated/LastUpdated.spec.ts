import LastUpdated from './LastUpdated.vue';

import { setupTest } from '@/utils/test';
import type { PiniaStateTree } from '@/store/storeType';
import { createTestingPinia } from '@pinia/testing';
import { useAppStore } from '@/store/app/appStore';
import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';

describe('LastUpdated.vue', () => {
    const dateFnsFormatMock = vi.fn();

    const shallowMountWithAppState = setupTest(
        (localVue: VueConstructor, appState: Partial<PiniaStateTree['app']> = {}) => {
            return shallowMount(LastUpdated, {
                localVue,
                pinia: createTestingPinia({
                    stubActions: false,
                    initialState: {
                        app: appState,
                    },
                }),
                mocks: {
                    $filters: {
                        dateFnsFormat: dateFnsFormatMock,
                    },
                },
            });
        }
    );

    beforeAll(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.resetAllMocks();
    });

    it('should show nothing when no request was made yet', () => {
        const wrapper = shallowMountWithAppState();

        expect(wrapper.text()).toBe('');
    });

    it('should show nothing when initialized after the last request', () => {
        const wrapper = shallowMountWithAppState({ lastUpdated: 1 });

        expect(wrapper.text()).toBe('');
    });

    it('should show loading state when requests are pending', () => {
        const wrapper = shallowMountWithAppState({ requestCount: 1 });

        expect(wrapper.text()).toBe('Bezig met opslaan');
    });

    it('should set last updated state when requests have successfully finished after initialization', async () => {
        vi.setSystemTime(new Date('2021-01-01 12:00'));
        const wrapper = shallowMountWithAppState();

        vi.setSystemTime(new Date('2021-01-01 12:01'));
        const appStore = useAppStore();
        appStore.handleRequestComplete(true);
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('Laatst opgeslagen');
        expect(dateFnsFormatMock).toHaveBeenCalledWith(appStore.lastUpdated, 'HH:mm');
    });

    it('should not set last updated state when requests have errors after initialization', async () => {
        vi.setSystemTime(new Date('2021-01-01 12:00'));
        const wrapper = shallowMountWithAppState();

        vi.setSystemTime(new Date('2021-01-01 12:01'));
        const appStore = useAppStore();
        appStore.handleRequestComplete(false);

        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toBe('');
        expect(dateFnsFormatMock).toHaveBeenCalledTimes(0);
    });
});
