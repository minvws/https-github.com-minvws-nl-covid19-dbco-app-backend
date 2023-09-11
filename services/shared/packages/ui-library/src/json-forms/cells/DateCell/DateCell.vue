<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="date"
        :id="id"
        :invalid="!cell.isValid"
        :value="cell.data || ''"
        :disabled="!enabled"
        :autoFocus="uiOptions.focus"
        :aria-errormessage="errorId"
        @blur="handleInputBlur"
    />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { Input } from '../../../components';
import { useJsonFormsCell, useId, useErrorId, useUiOptions } from '../../composition';
import type { ControlElement } from '../../types';

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'date'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<string, 'date'>(props);

        function handleInputBlur(event: BlurEvent<HTMLInputElement>) {
            handleChange(props.path, event.target.value === '' ? undefined : event.target.value);
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleInputBlur,
        };
    },
});
</script>
