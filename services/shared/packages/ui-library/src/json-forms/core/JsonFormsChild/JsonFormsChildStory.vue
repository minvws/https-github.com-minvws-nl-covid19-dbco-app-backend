<template>
    <Stack direction="row" class="tw-items-start">
        <JsonFormsChild
            class="tw-w-full"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            @change="handleFormChange"
            @childFormChange="handleChildFormChange"
            v-on="$listeners"
        />
        <JsonFormsEditor
            class="tw-w-full"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            @change="handleEditorChange"
        />
    </Stack>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { set } from 'lodash';
import { Stack } from '../../../components';
import { useJsonFormsStoryState, useDebugFormActionHandler } from '../../stories/utils';
import type { ChildFormChangeEvent, FormData } from '../../types';
import JsonFormsEditor from '../../tools/JsonFormsEditor/JsonFormsEditor.vue';
import JsonFormsChild from './JsonFormsChild.vue';
import { provideFormActionHandler } from '../JsonForms/provide';

export default defineComponent({
    components: { Stack, JsonFormsChild, JsonFormsEditor },
    props: {
        ...JsonFormsChild.props,
    },
    setup(props) {
        const { data, ...rest } = useJsonFormsStoryState(props);
        const { debugFormActionHandler } = useDebugFormActionHandler({ defaultData: data.value as FormData });

        // The "form action handler" would normally be provided by the `JsonForms` component
        provideFormActionHandler({ formActionHandler: debugFormActionHandler });

        const handleChildFormChange = (event: ChildFormChangeEvent) => {
            const { data: newData, path } = event;
            set(data.value, path, newData);
        };

        return {
            data,
            ...rest,
            handleChildFormChange,
        };
    },
});
</script>
