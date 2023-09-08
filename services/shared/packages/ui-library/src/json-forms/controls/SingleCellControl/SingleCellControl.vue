<template>
    <DispatchCell
        :schema="control.schema"
        :uischema="controlUiSchema"
        :path="control.path"
        :enabled="control.enabled"
        :renderers="control.renderers"
        :cells="control.cells"
    />
</template>

<script lang="ts">
import { DispatchCell, rendererProps } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsControl, hasDeterminedCell } from '../../composition';
import type { ControlElement, ControlElementCore } from '../../types';

/**
 * A Control for rendering a single cell.
 * This can be used for testing and documentation purposes.
 */
export default defineComponent({
    components: {
        DispatchCell,
    },
    props: {
        ...rendererProps<ControlElement>(),
    },
    setup(props) {
        const { control } = useJsonFormsControl(props);

        if (!hasDeterminedCell(control, props.config)) {
            console.warn(
                'No applicable cell found.',
                { uischema: props.uischema, schema: props.schema },
                `
This \`SingleCellControl\` is most likely used with a filtered cells registry to ensure that only one cell is applicable. 
However either the tester is not configured correctly or the \`schema\` / \`uiSchema\` does not match on the expected values.`
            );
        }

        return {
            control,
            controlUiSchema: control.value.uischema as ControlElementCore,
        };
    },
});
</script>
