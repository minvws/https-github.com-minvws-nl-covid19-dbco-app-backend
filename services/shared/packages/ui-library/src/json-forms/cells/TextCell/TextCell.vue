<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="text"
        :id="id"
        :invalid="!cell.isValid"
        :value="cell.data || ''"
        :disabled="!enabled"
        :autoFocus="uiOptions.focus"
        :placeholder="uiOptions.placeholder"
        :maxLength="schema.maxLength"
        :aria-errormessage="errorId"
        @change="handleInputChange"
    />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { Input } from '../../../components';
import { useJsonFormsCell, useUiOptions, useId, useErrorId } from '../../composition';
import type { ControlElement } from '../../types';

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'string'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<string, 'string'>(props);

        function handleInputChange(event: ChangeEvent<HTMLInputElement>) {
            handleChange(props.path, event.target.value === '' ? undefined : event.target.value);
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleInputChange,
        };
    },
});
</script>
