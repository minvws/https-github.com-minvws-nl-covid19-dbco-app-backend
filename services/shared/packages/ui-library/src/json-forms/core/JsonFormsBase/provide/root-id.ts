import { inject, provide } from 'vue';
import { uniqueId } from 'lodash';

const key = Symbol('json-forms-root-id');

export function provideRootId() {
    const rootId = uniqueId('json-forms-');
    provide(key, rootId);
    return { rootId };
}

export function injectRootId() {
    return {
        rootId: inject(key) as string,
    };
}
