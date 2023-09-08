import { mount } from '@vue/test-utils';
import { decorateWrapper } from '../../test';
import { createDefaultLocalVue } from '../../test/local-vue';
import JsonForms from '../core/JsonForms/JsonForms.vue';
import type { emits } from '../core/JsonForms/emits';
import type { Props as JsonFormsProps } from '../core/JsonForms/props';

export interface JsonFormsTestConfig extends JsonFormsProps {
    onChange?: (typeof emits)['change'];
    onFormLink?: (typeof emits)['formLink'];
}
export function createJsonFormsTest({ onChange, onFormLink, ...propsData }: JsonFormsTestConfig) {
    return decorateWrapper(
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        mount(JsonForms as any /* fixes Volar type issue */, {
            localVue: createDefaultLocalVue(),
            propsData,
            listeners: {
                change: onChange || vi.fn(),
                formLink: onFormLink || vi.fn(),
            },
        })
    );
}
