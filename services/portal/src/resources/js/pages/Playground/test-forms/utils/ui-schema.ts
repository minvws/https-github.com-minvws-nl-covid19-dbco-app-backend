import type { UiSchema } from '@dbco/ui-library';
import { cloneDeep, isObject } from 'lodash';

export function prefixScopes<T extends Record<string, any>>(data: T, prefix: string) {
    if (!data || !isObject(data)) return data;

    type K = keyof typeof data;

    Object.entries(data).forEach(([key, value]) => {
        if (key === 'options') {
            return;
        }

        if (key === 'scope' && typeof value === 'string') {
            (data[key as K] as any) = value.replace(/^#\//, prefix);
        } else if (isObject(value)) {
            (data[key as K] as any) = prefixScopes(value, prefix);
        }
    });

    return data;
}

export function prefixScope(uiSchema: UiSchema, scopePrefix: string) {
    if (!/^#\//.test(scopePrefix)) {
        throw new Error('new scope prefix needs to start with "#/');
    }
    const uiSchemaClone = cloneDeep(uiSchema);
    return prefixScopes(uiSchemaClone, scopePrefix);
}
