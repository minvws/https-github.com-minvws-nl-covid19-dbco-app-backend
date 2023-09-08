import { vi } from 'vitest';
import type { VueConstructor } from 'vue';
import type Vue from 'vue';

/** This setting is neccesary because some component tests
 * may show the ""You are running Vue in development mode"
 * warning
 */
vi.mock('vue', async () => {
    const vue = await vi.importActual<{ default: VueConstructor<Vue> }>('vue');
    vue.default.config.productionTip = false;
    vue.default.config.devtools = false;
    return vue;
});

// Mock the interceptor because its dependency on the store makes tests fail
vi.mock('@/interceptors/errorInterceptor', () => ({
    errorInterceptor: () => {},
}));

vi.mock('@/interceptors/timeoutInterceptor', () => ({
    timeoutInterceptor: () => {},
}));

vi.mock('@/interceptors/lastUpdatedInterceptor', () => ({
    lastUpdatedErrorInterceptor: () => {},
    lastUpdatedRequestInterceptor: () => {},
    lastUpdatedResponseInterceptor: () => {},
}));
