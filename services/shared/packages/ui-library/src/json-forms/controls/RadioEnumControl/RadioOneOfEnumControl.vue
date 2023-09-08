<template>
    <RadioGroup :control="control" :handleChange="handleChange" />
</template>

<script lang="ts">
import type { ControlElement } from '../../types';
import RadioGroup from './RadioGroup.vue';
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsOneOfEnumControl } from '../../composition';

export default defineComponent({
    components: {
        RadioGroup,
    },
    props: {
        ...rendererProps<ControlElement<'enum'>>(),
    },
    setup(props) {
        return useJsonFormsOneOfEnumControl<string | number | boolean, 'enum'>(props);
    },
});
</script>
