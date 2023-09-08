<template>
    <ControlWrapper :control="control">
        <VStack spacing="2">
            <BooleanCell
                :schema="control.schema"
                :uischema="control.uischema"
                :path="control.path"
                :enabled="control.enabled"
                :renderers="control.renderers"
                :cells="control.cells"
            >
                {{ control.label }}
            </BooleanCell>

            <ControlErrors :control="control" />
        </VStack>
    </ControlWrapper>
</template>

<script lang="ts">
import { DispatchCell, rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { VStack } from '../../../components';
import BooleanCell from '../../cells/BooleanCell/BooleanCell.vue';
import type { ControlElement } from '../../types';
import { useErrors, useJsonFormsControl } from '../../composition';
import ControlErrors from '../ControlErrors/ControlErrors.vue';
import ControlLabel from '../ControlLabel/ControlLabel.vue';
import ControlWrapper from '../ControlWrapper/ControlWrapper.vue';

export default defineComponent({
    components: {
        BooleanCell,
        VStack,
        DispatchCell,
        ControlLabel,
        ControlErrors,
        ControlWrapper,
    },
    props: {
        ...rendererProps<ControlElement<'boolean'>>(),
    },
    setup(props) {
        const { control } = useJsonFormsControl<boolean, 'boolean'>(props);
        const errors = useErrors(control);

        return {
            control,
            errors,
        };
    },
});
</script>
