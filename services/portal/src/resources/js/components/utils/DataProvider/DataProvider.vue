<template>
    <div>
        <slot />
    </div>
</template>

<script lang="ts">
/**
 * This component can be used to provide variables to child components, which can inject them
 */

import { defineComponent } from 'vue';

export type DataProviderIndexProps = {
    index: number;
    getIndex: () => number;
};

export default defineComponent({
    name: 'DataProvider',
    computed: {
        providedValues(): any {
            // Provides both the static value and a dynamic value
            // For example, when index is given, both 'index' and 'getIndex()' will be provided
            return Object.entries(this.$attrs).reduce(
                (acc, [key, value]) => ({
                    ...acc,
                    [key]: value,
                    [(this as any).getMethodName(key)]: () => this.$attrs[key],
                }),
                {}
            );
        },
    },
    methods: {
        // Example: variableName => getVariableName
        getMethodName(key: string) {
            return `get${key.charAt(0).toUpperCase()}${key.slice(1)}`;
        },
    },
    provide() {
        return (this as any).providedValues;
    },
});
</script>
