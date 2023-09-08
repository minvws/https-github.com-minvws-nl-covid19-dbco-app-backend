import type { Component } from 'vue';
import { renderers } from '../core/JsonFormsBase/renderers';
import type { Writeable } from '../../types/helpers';
import { createJsonFormsBaseTest } from './create-json-forms-base-test';
import type { JsonFormsBaseTestConfig } from './create-json-forms-base-test';

export interface JsonFormsControlTestConfig extends JsonFormsBaseTestConfig {
    control: Component;
    useFilteredControls?: boolean; // defaults to true, can be turned off if the control itself depends on other controls
}

export function createJsonFormsControlTest({ control, useFilteredControls, ...rest }: JsonFormsControlTestConfig) {
    const filteredRenderers = renderers.filter((registry) => registry.renderer === control);

    if (!filteredRenderers.length) {
        throw new Error(
            `No control registry was found! Either the control is not registered or the tester did not match the schema.`
        );
    }
    return createJsonFormsBaseTest({
        ...rest,
        renderers: useFilteredControls !== false ? filteredRenderers : (renderers as Writeable<typeof renderers>),
    });
}
