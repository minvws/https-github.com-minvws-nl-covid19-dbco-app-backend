<template>
    <FormulateForm v-bind="$attrs" v-on="$listeners" v-model="model" />
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

interface PropValue {
    [key: string]: any;
}

/**
 * Can be used in the defineComponent generics for components which use the rootModel injectable
 */
export type RootModelProviderProps = { rootModel: () => PropValue };

export default defineComponent({
    name: 'FormulateFormWrapper',
    inheritAttrs: false,
    props: {
        value: {
            type: Object as PropType<PropValue>,
            required: true,
        },
    },
    computed: {
        model: {
            get() {
                return this.value;
            },
            set(val: any) {
                this.$emit('input', val);
            },
        },
    },
    provide() {
        return {
            // Provides original data model for custom form elements
            // Makes sure we receive the original values in them
            // This needs to be a function to ensure reactivity
            rootModel: () => this.value,
        };
    },
});
</script>
