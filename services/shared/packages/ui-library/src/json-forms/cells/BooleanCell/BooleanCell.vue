<template>
    <Checkbox
        :id="id"
        :invalid="!cell.isValid"
        :checked="!!cell.data"
        :autoFocus="uiOptions.focus"
        :disabled="!enabled"
        :aria-errormessage="errorId"
        @change="handleCheckboxChange"
    >
        <slot />
    </Checkbox>
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { Checkbox } from '../../../components';
import { useJsonFormsCell, useId, useUiOptions, useErrorId } from '../../composition';
import type { ControlElement } from '../../types';

export default defineComponent({
    components: {
        Checkbox,
    },
    props: {
        ...rendererProps<ControlElement<'boolean'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<boolean, 'boolean'>(props);

        function handleCheckboxChange(event: ChangeEvent<HTMLInputElement>) {
            handleChange(props.path, event.target.checked);
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleCheckboxChange,
        };
    },
});
</script>
