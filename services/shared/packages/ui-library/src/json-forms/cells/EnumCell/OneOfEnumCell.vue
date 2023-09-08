<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Select :cell="cell" :handleChange="handleChange" />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import type { ControlElement } from '../../types';
import { useJsonFormsOneOfEnumCell } from '../../composition';
import Select from './Select.vue';

export default defineComponent({
    components: {
        Select,
    },
    props: rendererProps<ControlElement<'enum'>>(),
    setup(props) {
        return { ...useJsonFormsOneOfEnumCell<unknown, 'enum'>(props) };
    },
});
</script>
