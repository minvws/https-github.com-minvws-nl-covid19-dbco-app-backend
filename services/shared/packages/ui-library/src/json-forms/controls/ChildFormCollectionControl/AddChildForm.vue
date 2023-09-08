<template>
    <div>
        <JsonFormsBase
            @change="handleChange"
            :class="['tw-mb-4', isCreatingNewItem ? ['tw-opacity-50'] : []]"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
        />
        <Button :loading="isCreatingNewItem" @click="handleNewItemSubmit" type="submit">Submit</Button>
    </div>
</template>

<script lang="ts">
import type { FormChangeEvent, FormRequestConfig, FormRequestData, FormData, JsonSchema, UiSchema } from '../../types';
import type { DefineComponent, PropType } from 'vue';
import { ref, defineComponent, toRef } from 'vue';
import { Button } from '../../../components';
import type { JsonSchema7 } from '@jsonforms/core';
import { createDefaultValue } from '@jsonforms/core';
import { injectFormActionHandler } from '../../core/JsonForms/provide';

export default defineComponent({
    components: {
        Button,
        JsonFormsBase: (() => {
            return import('../../core/JsonFormsBase/JsonFormsBase.vue').then((x) => x.default);
        }) as unknown as DefineComponent,
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
        createRequestConfig: {
            type: Object as PropType<FormRequestConfig>,
            required: true,
        },
    },
    emits: {
        /* c8 ignore next */
        create: (data: FormData) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    setup(props, { emit }) {
        const createRequestConfig = toRef(props, 'createRequestConfig');
        const { formActionHandler } = injectFormActionHandler();
        const data = ref<FormRequestData>(createDefaultValue(props.schema as JsonSchema7));
        const isCreatingNewItem = ref(false);

        const handleChange = (change: FormChangeEvent<FormRequestData>) => {
            data.value = change.data;
        };

        const handleNewItemSubmit = async () => {
            if (!formActionHandler) {
                console.warn('No form action handler found');
                return;
            }

            isCreatingNewItem.value = true;

            try {
                const newItem = await formActionHandler.create(createRequestConfig.value, data.value);
                emit('create', newItem);
                data.value = createDefaultValue(props.schema as JsonSchema7);
            } catch (error) {
                console.error(error);
            }

            isCreatingNewItem.value = false;
        };

        return {
            data,
            handleChange,
            handleNewItemSubmit,
            isCreatingNewItem,
        };
    },
});
</script>
