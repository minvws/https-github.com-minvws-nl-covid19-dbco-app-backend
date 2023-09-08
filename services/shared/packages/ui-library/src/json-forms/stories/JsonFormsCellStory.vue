<template>
    <JsonFormsBaseStory
        :data="data"
        :schema="schema"
        :uiSchema="uiSchema"
        :additionalErrors="additionalErrors"
        :cells="cells"
        :renderers="renderers"
    />
</template>

<script lang="ts">
import type { Component, PropType } from 'vue';
import { defineComponent } from 'vue';
import JsonFormsBaseStory from '../core/JsonFormsBase/JsonFormsBaseStory.vue';
import { SingleCellControl } from '../controls';
import { cells } from '../core/JsonFormsBase/cells';

const { data, schema, uiSchema, additionalErrors } = JsonFormsBaseStory.props;

export default defineComponent({
    props: {
        cell: {
            type: Object as PropType<Component>,
            required: true,
        },
        data,
        schema,
        uiSchema,
        additionalErrors,
    },
    components: { JsonFormsBaseStory },
    setup({ cell }) {
        const filteredCells = cells.filter((registry) => registry.cell === cell);

        return {
            cells: filteredCells,
            renderers: [
                {
                    renderer: SingleCellControl,
                    tester: () => 999,
                },
            ],
        };
    },
});
</script>
