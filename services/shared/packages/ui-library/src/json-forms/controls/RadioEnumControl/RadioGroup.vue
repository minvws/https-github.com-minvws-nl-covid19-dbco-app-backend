<template>
    <ControlWrapper :control="control" as="fieldset" class="tw-reset-fieldset">
        <VStack spacing="2">
            <ControlLabel :control="control" />

            <RadioGroup
                @change="handleRadioGroupChange"
                as="div"
                :value="valueToString(control.data)"
                :variant="radioVariant"
            >
                <Stack spacing="2" :direction="radioVariant === 'plain' ? 'column' : 'row'">
                    <Radio
                        v-for="option in control.options"
                        :key="option.value"
                        :value="valueToString(option.value)"
                        :disabled="!control.enabled"
                        :invalid="!!control.errors.length"
                    >
                        {{ option.label }}
                    </Radio>
                </Stack>
            </RadioGroup>

            <ControlErrors :control="control" />
        </VStack>
    </ControlWrapper>
</template>

<script lang="ts">
import type { RadioVariant } from '../../../components';
import { VStack, Stack, RadioGroup, Radio } from '../../../components';
import type { ControlBindings, JsonFormsChangeHandler, JsonFormsEnumControl } from '../../types';
import ControlErrors from '../ControlErrors/ControlErrors.vue';
import ControlLabel from '../ControlLabel/ControlLabel.vue';
import ControlWrapper from '../ControlWrapper/ControlWrapper.vue';
import type { PropType } from 'vue';
import { computed, defineComponent, toRef } from 'vue';
import { useUiOptions } from '../../composition';
import { isSchemaType, stringToValue, valueToString } from '../../utils';

export default defineComponent({
    components: {
        ControlWrapper,
        RadioGroup,
        Radio,
        Stack,
        VStack,
        ControlLabel,
        ControlErrors,
    },
    props: {
        control: {
            type: Object as PropType<ControlBindings<JsonFormsEnumControl, string | number | boolean, 'enum'>>,
            required: true,
        },
        handleChange: {
            type: Function as PropType<JsonFormsChangeHandler>,
            required: true,
        },
    },
    setup(props) {
        const control = toRef(props, 'control');
        const type = control.value.schema.type;
        const uiOptions = useUiOptions(control);
        const radioVariant = computed<RadioVariant>(() =>
            uiOptions.value.format === 'radio-button' ? 'button' : 'plain'
        );

        /* c8 ignore next 3 */
        if (!isSchemaType(type, ['string', 'number', 'integer', 'boolean'])) {
            throw new Error('EnumCell only supports string, number, integer and boolean types');
        }

        const handleRadioGroupChange = (event: ChangeEvent<HTMLInputElement>) => {
            props.handleChange(control.value.path, stringToValue(event.target.value, type));
        };

        return { control, handleRadioGroupChange, valueToString, radioVariant };
    },
});
</script>
