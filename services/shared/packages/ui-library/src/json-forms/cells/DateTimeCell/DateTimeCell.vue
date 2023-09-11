<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="datetime-local"
        :id="id"
        :value="value"
        :invalid="!cell.isValid"
        :disabled="!enabled"
        :autoFocus="uiOptions.focus"
        :aria-errormessage="errorId"
        @blur="handleInputBlur"
    />
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { computed, defineComponent } from 'vue';
import { Input } from '../../../components';
import { useJsonFormsCell, useId, useErrorId, useUiOptions } from '../../composition';
import type { ControlElement } from '../../types';

const dropSecondsAndTimezone = (value: string) => value.substring(0, 16);

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'datetime'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<string, 'datetime'>(props);
        const value = computed(() => dropSecondsAndTimezone(cell.value.data || ''));

        function handleInputBlur(event: BlurEvent<HTMLInputElement>) {
            handleChange(
                props.path,
                event.target.value === '' ? undefined : `${dropSecondsAndTimezone(event.target.value)}:00`
            );
        }

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleInputBlur,
            value,
        };
    },
});
</script>
