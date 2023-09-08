// Adds missing types
// @see: https://github.com/probil/v-mask/issues/489
declare module 'v-mask' {
    import type { PluginFunction, PluginObject, DirectiveHook } from 'vue';

    interface VueMaskDirectiveType {
        bind: DirectiveHook;
        componentUpdated: DirectiveHook;
        unbind: DirectiveHook;
    }

    export const VueMaskDirective: VueMaskDirectiveType;

    declare const _default: PluginObject<unknown> | PluginFunction<unknown>;

    export default _default;
}
