<template>
    <!-- eslint-disable vuejs-accessibility/form-control-has-label vuejs-accessibility/no-autofocus -->
    <Select
        :id="id"
        :invalid="!cell.isValid"
        :disabled="!cell.enabled"
        :autoFocus="uiOptions.focus"
        :placeholder="uiOptions.placeholder || ''"
        :aria-errormessage="errorId"
        @blur="handleSelectBlur"
    >
        <option
            v-for="option in cell.options"
            :key="option.value"
            :value="valueToString(option.value)"
            :selected="option.value === cell.data"
            :label="option.label"
        />
    </Select>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRef } from 'vue';
import { Select } from '../../../components';
import { isSchemaType, stringToValue, valueToString } from '../../utils';
import { useId, useErrorId, useUiOptions } from '../../composition';
import type { CellBindings, JsonFormsChangeHandler, JsonFormsEnumCell } from '../../types';

export default defineComponent({
    components: {
        Select,
    },
    props: {
        cell: {
            type: Object as PropType<CellBindings<JsonFormsEnumCell, unknown, 'enum'>>,
            required: true,
        },
        handleChange: {
            type: Function as PropType<JsonFormsChangeHandler>,
            required: true,
        },
    },
    setup(props) {
        const cell = toRef(props, 'cell');
        const type = cell.value.schema.type;

        /* c8 ignore next 3 */
        if (!isSchemaType(type, ['string', 'number', 'integer', 'boolean'])) {
            throw new Error('EnumCell only supports string, number, integer and boolean types');
        }

        const handleSelectBlur = (event: FocusEvent<HTMLSelectElement>) => {
            props.handleChange(cell.value.path, stringToValue(event.target.value, type));
        };

        return {
            id: useId(cell),
            errorId: useErrorId(cell),
            uiOptions: useUiOptions(cell),
            cell,
            handleSelectBlur,
            valueToString,
        };
    },
});
</script>
