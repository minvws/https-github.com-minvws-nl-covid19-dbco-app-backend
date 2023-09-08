<template>
    <Stack direction="row" class="tw-items-start">
        <JsonFormsBase
            class="tw-w-full"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            :additionalErrors="additionalErrors"
            :cells="cells"
            :renderers="renderers"
            :i18nResource="i18nResource"
            @change="handleFormChange"
            v-on="$listeners"
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
import JsonFormsBase from './JsonFormsBase.vue';
import JsonFormsEditor from '../../tools/JsonFormsEditor/JsonFormsEditor.vue';
import { useJsonFormsStoryState } from '../../stories/utils';

export default defineComponent({
    props: JsonFormsBase.props,
    components: { Stack, JsonFormsBase, JsonFormsEditor },
    setup(props) {
        return useJsonFormsStoryState(props);
    },
});
</script>
