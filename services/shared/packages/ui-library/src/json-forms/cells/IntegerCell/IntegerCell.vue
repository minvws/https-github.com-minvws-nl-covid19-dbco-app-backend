<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="number"
        :step="1"
        :id="id"
        :value="value"
        :invalid="!cell.isValid"
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
import { inputToInteger, valueToString } from '../../utils';
import type { ControlElement } from '../../types';

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'integer'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<number, 'integer'>(props);
        const value = computed(() => valueToString(cell.value.data));

        function handleInputChange(event: ChangeEvent<HTMLInputElement>) {
            handleChange(props.path, inputToInteger(event.target.value));
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
