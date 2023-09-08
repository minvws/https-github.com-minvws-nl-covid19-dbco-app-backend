<template>
    <ControlWrapper :control="control">
        <VStack spacing="2">
            <ControlLabel :control="control" />

            <DispatchCell
                :schema="control.schema"
                :uischema="controlUiSchema"
                :path="control.path"
                :enabled="control.enabled"
                :renderers="control.renderers"
                :cells="control.cells"
            />

            <ControlErrors :control="control" />
        </VStack>
    </ControlWrapper>
</template>

<script lang="ts">
import { DispatchCell, rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsControl, hasDeterminedCell, useErrors } from '../../composition';
import { VStack } from '../../../components';
import ControlErrors from '../ControlErrors/ControlErrors.vue';
import ControlLabel from '../ControlLabel/ControlLabel.vue';
import ControlWrapper from '../ControlWrapper/ControlWrapper.vue';
import type { ControlElement, ControlElementCore } from '../../types';

export default defineComponent({
    components: {
        VStack,
        DispatchCell,
        ControlLabel,
        ControlErrors,
        ControlWrapper,
    },
    props: {
        ...rendererProps<ControlElement>(),
    },
    setup(props) {
        const { control } = useJsonFormsControl(props);
        const errors = useErrors(control);

        if (!hasDeterminedCell(control, props.config)) {
            console.warn('No applicable cell found.', { uischema: props.uischema, schema: props.schema });
        }

        return {
            control,
            errors,
            controlUiSchema: control.value.uischema as ControlElementCore,
        };
    },
});
</script>
