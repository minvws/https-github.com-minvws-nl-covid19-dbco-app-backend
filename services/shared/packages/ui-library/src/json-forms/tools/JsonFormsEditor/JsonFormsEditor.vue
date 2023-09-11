<template>
    <TabsContext>
        <TabList>
            <Tab>Schema</Tab>
            <Tab>UI Schema</Tab>
            <Tab>Data</Tab>
            <Tab v-if="additionalErrors">Additional Errors</Tab>
        </TabList>

        <TabPanels class="tw-py-2">
            <TabPanel>
                <FixedTypeJsonEditor :value="schema" @change="(updatedValue) => emitChange({ schema: updatedValue })" />
            </TabPanel>
            <TabPanel>
                <FixedTypeJsonEditor
                    :value="uiSchema"
                    @change="(updatedValue) => emitChange({ uiSchema: updatedValue })"
                />
            </TabPanel>
            <TabPanel>
                <FixedTypeJsonEditor :value="data" @change="(updatedValue) => emitChange({ data: updatedValue })" />
            </TabPanel>
            <TabPanel v-if="additionalErrors">
                <FixedTypeJsonEditor
                    :value="additionalErrors"
                    @change="(updatedValue) => emitChange({ additionalErrors: updatedValue })"
                />
            </TabPanel>
        </TabPanels>
    </TabsContext>
</template>

<script lang="ts">
import 'vanilla-jsoneditor/themes/jse-theme-dark.css';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

import FixedTypeJsonEditor from '../FixedTypeJsonEditor/FixedTypeJsonEditor.vue';
import { Tab, TabsContext, TabList, TabPanels, TabPanel } from '../../../components';
import type { JsonFormsEditorChangeEvent } from './types';
import type { FormError, UiSchema, JsonSchema } from '../../types';

export default defineComponent({
    components: {
        FixedTypeJsonEditor,
        Tab,
        TabsContext,
        TabList,
        TabPanels,
        TabPanel,
    },
    props: {
        schema: {
            required: true,
            type: Object as PropType<JsonSchema>,
        },
        uiSchema: {
            required: true,
            type: Object as PropType<UiSchema>,
        },
        data: FixedTypeJsonEditor.props.value,
        additionalErrors: {
            type: Array as PropType<FormError[]>,
        },
    },
    setup(props, { emit }) {
        const emitChange = (event: JsonFormsEditorChangeEvent) => {
            emit('change', event);
        };
        return { emitChange };
    },
});
</script>
