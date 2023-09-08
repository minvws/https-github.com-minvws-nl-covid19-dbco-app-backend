import { registerDirectives } from '@dbco/ui-library';
import { createLocalVue } from '@vue/test-utils';

export function createDefaultLocalVue() {
    const localVue = createLocalVue();

    registerDirectives(localVue);

    return localVue;
}
