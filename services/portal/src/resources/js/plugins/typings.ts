import type { PluginObject } from 'vue';

const asAny = (value: any) => value as any;
const asDefined = <T>(value: T) => value as NonNullable<T>;

export const typingHelpers = {
    any: asAny,
    defined: asDefined,
};

const TypingHelpers: PluginObject<void> = {
    install(Vue) {
        Vue.prototype.$as = typingHelpers;
    },
};

export default TypingHelpers;
