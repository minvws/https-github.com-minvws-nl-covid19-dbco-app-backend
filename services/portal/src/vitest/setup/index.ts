import './mocks';
import './window';
import matchers from '@testing-library/vue';
import { throwOnConsoleLog } from '@dbco/ui-library/test/throw-on-console-log';

expect.extend(matchers as any);

throwOnConsoleLog({
    logMethods: ['warn', 'error'],
    ignoreMessages: [
        // @see: https://github.com/molgenis/molgenis-frontend/issues/466
        '[BootstrapVue warn]: tooltip - The provided target is no valid HTML element.',
        // Issue with SSR usage, or in this case during test usage of the BS toast
        // @see: https://github.com/LinusBorg/portal-vue/issues/204
        '[portal-vue]: Target b-toaster-top-center already exists',
    ],
});
