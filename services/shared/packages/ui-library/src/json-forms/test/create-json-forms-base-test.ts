import { mount } from '@vue/test-utils';
import JsonFormsBase from '../core/JsonFormsBase/JsonFormsBase.vue';
import { createDefaultLocalVue } from '../../test/local-vue';
import type { Props as JsonFormsBaseProps } from '../core/JsonFormsBase/props';
import type { emits } from '../core/JsonFormsBase/emits';
import { decorateWrapper } from '../../test';

export interface JsonFormsBaseTestConfig extends JsonFormsBaseProps {
    onChange?: (typeof emits)['change'];
    onFormLink?: (typeof emits)['formLink'];
    onChildFormChange?: (typeof emits)['childFormChange'];
}

export function createJsonFormsBaseTest({
    onChange,
    onFormLink,
    onChildFormChange,
    ...propsData
}: JsonFormsBaseTestConfig) {
    return decorateWrapper(
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        mount(JsonFormsBase as any /* fixes Volar type issue */, {
            localVue: createDefaultLocalVue(),
            propsData,
            listeners: {
                change: onChange || vi.fn(),
                formLink: onFormLink || vi.fn(),
                childFormChange: onChildFormChange || vi.fn(),
            },
        })
    );
}
