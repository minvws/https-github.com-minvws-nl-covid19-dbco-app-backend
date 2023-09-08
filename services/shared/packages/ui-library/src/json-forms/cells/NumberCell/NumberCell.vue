<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="number"
        :step="0.1"
        :id="id"
        :invalid="!cell.isValid"
        :value="value"
        :disabled="!enabled"
        :autoFocus="uiOptions.focus"
        :aria-errormessage="errorId"
        @change="handleInputChange"
    />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { computed, defineComponent } from 'vue';
import { Input } from '../../../components';
import { useJsonFormsCell, useId, useErrorId, useUiOptions } from '../../composition';
import { inputToNumber, valueToString } from '../../utils';
import type { ControlElement } from '../../types';

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'number'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<number, 'number'>(props);
        const value = computed(() => valueToString(cell.value.data));

        function handleInputChange(event: ChangeEvent<HTMLInputElement>) {
            handleChange(props.path, inputToNumber(event.target.value));
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            value,
            cell,
            handleInputChange,
        };
    },
});
</script>
