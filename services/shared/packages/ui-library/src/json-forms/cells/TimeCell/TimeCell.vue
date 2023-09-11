<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Input
        type="time"
        :id="id"
        :value="cell.data || ''"
        :invalid="!cell.isValid"
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

/**
 * AJV 'time' format expects HH:mm:ss while <input type='time'> may only return HH:mm.
 * Therefore we append ':00' when the seconds are missing.
 */
const appendSeconds = (value: string | undefined) => {
    if (typeof value !== 'string' || value === '') return undefined;
    const splitValue = value.split(':');
    if (splitValue.length === 2) {
        splitValue.push('00');
    }
    return splitValue.join(':');
};

export default defineComponent({
    components: {
        Input,
    },
    props: {
        ...rendererProps<ControlElement<'time'>>(),
    },
    setup(props) {
        const { handleChange, cell } = useJsonFormsCell<string, 'time'>(props);

        function handleInputBlur(event: BlurEvent<HTMLInputElement>) {
            handleChange(props.path, appendSeconds(event.target.value));
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
