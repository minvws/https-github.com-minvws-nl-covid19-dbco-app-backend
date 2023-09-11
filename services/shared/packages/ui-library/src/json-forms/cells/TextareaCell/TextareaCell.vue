<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Textarea
        :id="id"
        :invalid="!cell.isValid"
        :value="cell.data || ''"
        :disabled="!enabled"
        :autoFocus="uiOptions.focus"
        :placeholder="uiOptions.placeholder"
        :maxLength="schema.maxLength"
        :aria-errormessage="errorId"
        @change="handleTextareaChange"
    />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { Textarea } from '../../../components';
import { useJsonFormsCell, useId, useErrorId, useUiOptions } from '../../composition';
import type { ControlElement } from '../../types';

export default defineComponent({
    name: 'TextareaCell',
    components: {
        Textarea,
    },
    props: {
        ...rendererProps<ControlElement<'string'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<string, 'string'>(props);

        function handleTextareaChange(event: ChangeEvent<HTMLTextAreaElement>) {
            handleChange(props.path, event.target.value === '' ? undefined : event.target.value);
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleTextareaChange,
        };
    },
});
</script>
