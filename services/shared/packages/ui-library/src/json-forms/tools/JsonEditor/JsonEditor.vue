<template>
    <div>
        <JsonEditorVue
            class="jse-theme-dark tw-max-h-[800px] tw-flex tw-flex-col"
            mode="text"
            :content="content"
            :onRenderMenu="onRenderMenu"
            :onChange="onChange"
        />
        <JsonEditorErrorMessage v-if="errorMessage && errorMessage.length">
            {{ errorMessage }}
        </JsonEditorErrorMessage>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, watch, ref, defineComponent } from 'vue';
import JsonEditorVue from 'json-editor-vue';
import JsonEditorErrorMessage from './JsonEditorErrorMessage.vue';
import 'vanilla-jsoneditor/themes/jse-theme-dark.css';
import type { Content, ContentErrors, JSONValue, OnChange, OnRenderMenu, TextContent } from 'vanilla-jsoneditor';
import { isJSONContent } from 'vanilla-jsoneditor';
import { safeParse } from './utils';
import { cloneDeep, isEqual, isObject, isUndefined } from 'lodash';

export type { JSONValue };

/**
 * Converts a value to a valid JSON editor content type.
 * Also safeguards against `undefined`, which causes an infinite loop in the editor.
 */
function getEditorContent(value: unknown): Content {
    if (value === undefined) return { text: '' };
    return { json: isObject(value) ? cloneDeep(value) : value } as Content;
}

export default defineComponent({
    components: {
        JsonEditorVue,
        JsonEditorErrorMessage,
    },
    props: {
        value: {
            required: true,
            validator: (value) => !isUndefined(value),
        },
        errorMessage: {
            type: String as PropType<string | null>,
        },
    },
    emits: {
        change: (newValue: JSONValue) => false, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    setup(props, { emit }) {
        const content = ref(getEditorContent(props.value) as unknown);
        const contentErrors = ref<ContentErrors | null>();

        const parsedValue = computed(() => {
            if (contentErrors.value) return null;
            const contentValue = isJSONContent(content.value)
                ? content.value.json
                : safeParse((content.value as TextContent).text);
            return contentValue === undefined ? null : contentValue;
        });

        watch(
            parsedValue,
            (newValue, oldValue) => {
                if (newValue !== null && !isEqual(newValue, oldValue) && !isEqual(newValue, props.value)) {
                    emit('change', cloneDeep(newValue as JSONValue));
                }
            },
            { deep: true }
        );

        watch(
            () => props.value,
            (newValue) => {
                if (!isEqual(parsedValue.value, newValue)) {
                    content.value = getEditorContent(newValue);
                }
            },
            { deep: true }
        );

        const onRenderMenu: OnRenderMenu = (items) => items.slice(4); // drop other edit formats

        const onChange: OnChange = (updatedContent, previousContent, { contentErrors: updatedContentErrors }) => {
            if (isEqual(content.value, updatedContent)) return;
            content.value = updatedContent as unknown;
            contentErrors.value = updatedContentErrors;
        };

        return { content, onRenderMenu, onChange };
    },
});
</script>
