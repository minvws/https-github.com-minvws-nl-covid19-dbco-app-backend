import { vi } from 'vitest';
import { default as Vue } from 'vue';
import { throwOnConsoleLog } from '../src/test/throw-on-console-log';

throwOnConsoleLog({
    logMethods: ['warn', 'error'],
});

/** This setting is neccesary because some component tests
 * may show the ""You are running Vue in development mode"
 * warning
 */
vi.mock('vue', async () => {
    const vue = await vi.importActual<typeof import('vue')>('vue');
    vue.default.config.productionTip = false;
    vue.default.config.devtools = false;

    return { ...vue, provide: vi.fn(vue.provide), inject: vi.fn(vue.inject) };
});

/**
 * I'm not entirely sure why this is needed, but it seems that just setting
 * these options in the mock itself is not enough to avoid these info messages:
 *
 * "Download the Vue Devtools extension for a better development experience:
 * https://github.com/vuejs/vue-devtools
 * You are running Vue in development mode.
 * Make sure to turn on production mode when deploying for production.
 * See more tips at https://vuejs.org/guide/deployment.html"
 */
Vue.config.productionTip = false;
Vue.config.devtools = false;
