<template>
    <Stack direction="row" class="tw-items-start">
        <JsonForms
            class="tw-w-full"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            :additionalErrors="additionalErrors"
            :i18nResource="i18nResource"
            :actionHandler="useActionHandler ? debugFormActionHandler : undefined"
            @change="handleFormChange"
        />
        <JsonFormsEditor
            class="tw-w-full"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            :additionalErrors="additionalErrors"
            @change="handleEditorChange"
        />
    </Stack>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Stack } from '../../../components';
import JsonFormsEditor from '../../tools/JsonFormsEditor/JsonFormsEditor.vue';
import JsonForms from './JsonForms.vue';
import { useJsonFormsStoryState, useDebugFormActionHandler } from '../../stories/utils';
import type { FormData } from '../../types';

const { data, schema, uiSchema, additionalErrors, i18nResource } = JsonForms.props;

export default defineComponent({
    components: { Stack, JsonForms, JsonFormsEditor },
    props: {
        data,
        schema,
        uiSchema,
        additionalErrors,
        i18nResource,
        useActionHandler: {
            type: Boolean,
            default: true,
        },
    },
    setup(props) {
        const { data, ...rest } = useJsonFormsStoryState(props);
        const { debugFormActionHandler } = useDebugFormActionHandler({ defaultData: data.value as FormData });

        return {
            data,
            ...rest,
            debugFormActionHandler,
        };
    },
});
</script>
