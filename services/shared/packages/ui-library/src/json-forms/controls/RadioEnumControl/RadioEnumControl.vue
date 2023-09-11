<template>
    <RadioGroup :control="control" :handleChange="handleChange" />
</template>

<script lang="ts">
import type { ControlElement } from '../../types';
import RadioGroup from './RadioGroup.vue';
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsEnumControl } from '../../composition';

export default defineComponent({
    components: {
        RadioGroup,
    },
    props: {
        ...rendererProps<ControlElement<'enum'>>(),
    },
    setup(props) {
        return useJsonFormsEnumControl<string | number | boolean, 'enum'>(props);
    },
});
</script>
