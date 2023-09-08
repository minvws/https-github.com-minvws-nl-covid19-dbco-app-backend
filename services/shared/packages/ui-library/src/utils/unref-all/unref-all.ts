import type { Ref } from 'vue';
import { unref } from 'vue';

export type Unref<T> = [T] extends [Ref] ? Ref['value'] : T;

export function unrefAll<T extends GenericObject>(values: T) {
    const unreffedValues: Partial<GenericObject> = {};
    Object.keys(values).forEach((key) => {
        unreffedValues[key] = unref(values[key]);
    });
    return unreffedValues as { [K in keyof T]: Unref<T[K]> };
}
