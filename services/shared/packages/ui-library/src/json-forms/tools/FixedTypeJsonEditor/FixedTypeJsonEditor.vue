<template>
    <JsonEditor :value="value" :errorMessage="errorMessage" @change="onChange" />
</template>

<script lang="ts">
import { isArray, isNumber, isPlainObject, isString } from 'lodash';
import type { PropType } from 'vue';
import { defineComponent, ref } from 'vue';
import type { JSONValue } from '../JsonEditor/JsonEditor.vue';
import JsonEditor from '../JsonEditor/JsonEditor.vue';

type EditorType = 'object' | 'array' | 'string' | 'number' | 'boolean';

function getEditorType(value: any): EditorType | null {
    if (typeof value === 'boolean') return 'boolean';
    if (isNumber(value)) return 'number';
    if (isString(value)) return 'string';
    if (isArray(value)) return 'array';
    if (isPlainObject(value)) return 'object';
    return null;
}

type Any = GenericObject | Array<unknown> | string | boolean | number;

export default defineComponent({
    components: {
        JsonEditor,
    },
    props: {
        value: {
            type: [Object, Array, String, Boolean, Number] as PropType<Any>,
            required: true,
        },
    },
    emits: {
        change: <T extends GenericObject>(newValue: T | Array<unknown>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    setup(props, { emit }) {
        const editorType = getEditorType(props.value);
        const errorMessage = ref<string | null>(null);

        if (!editorType) {
            throw new Error('The type of the value is not supported.');
        }

        const onChange = (newValue: JSONValue) => {
            if (getEditorType(newValue) !== editorType) {
                errorMessage.value = `The type of the content does not match the type of the original value (${editorType}).`;
                return;
            }
            errorMessage.value = null;
            emit('change', newValue);
        };

        return { onChange, errorMessage };
    },
});
</script>
