export {};

declare module 'vue' {
    /**
     * Augment global properties
     * @see: https://vuejs.org/guide/typescript/options-api.html#augmenting-global-properties
     */
    interface ComponentCustomProperties {
        /**
         * @deprecated This is an internal value that we should not really be using. It can be however useful for debugging purposes
         */
        _uid: string;

        $modal: {
            show: (params: {
                cancelTitle?: string;
                cancelVariant?: string;
                okOnly?: boolean;
                okTitle?: string;
                okVariant?: string;
                title?: string;
                centered?: boolean;
                text?: string;
                onCancel?: () => void;
                onConfirm?: () => void;
            }) => void;
            hide: () => void;
        };

        /**
         * @deprecated Vuex is deprecated, implementations need to move to Pinia
         * @see: https://pinia.vuejs.org/
         */
        $store: import('./utils/vuex').TypedStore;

        $formulate: import('@braid/vue-formulate').Formulate;

        $filters: ReturnType<typeof import('./filters/useFilters').useFilters>;

        $as: typeof import('./plugins/typings').typingHelpers;

        $cookies: import('vue-cookies').VueCookies;
    }

    // export interface GlobalComponents {
    //   add global component types here
    // }
}
