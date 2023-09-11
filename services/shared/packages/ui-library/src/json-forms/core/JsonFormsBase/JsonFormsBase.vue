<template>
    <div>
        <div v-if="schemaError" class="tw-border-2 tw-border-solid tw-border-red-700 tw-text-red-800 tw-p-2">
            <code>
                Json schema is invalid!
                <br /><br />
                {{ schemaError }}
            </code>
        </div>
        <JsonFormsVue2
            v-else
            :cells="cells"
            :data="data"
            :schema="localSchema"
            :uischema="uiSchema"
            :i18n="i18nState"
            :additionalErrors="validAdditionalErrors"
            :renderers="renderers"
            @change="handleChange"
        />
    </div>
</template>

<script lang="ts">
import type { UISchemaElement, JsonSchema as CoreJsonSchema } from '@jsonforms/core';
import type { Ref } from 'vue';
import type { FormChangeEvent, JsonSchema } from '../../types';
import { JsonForms as JsonFormsVue2 } from '@jsonforms/vue2';
import { computed, defineComponent, ref, toRef, watch } from 'vue';
import { cloneDeep, isEqual, isPlainObject, isString } from 'lodash';
import { provideTranslation, provideEventBus, provideRootId } from './provide';
import { emits } from './emits';
import { validateSchema } from '../../utils';
import { createI18n } from '../../i18n';
import { props } from './props';

export default defineComponent({
    props,
    emits,
    components: {
        JsonFormsVue2,
    },
    setup(props, { emit }) {
        const data = toRef(props, 'data');
        const uiSchema = toRef(props, 'uiSchema');
        const additionalErrors = toRef(props, 'additionalErrors');
        const schemaError = ref(validateSchema(props.schema));
        const localSchema = ref<JsonSchema>(schemaError.value ? {} : cloneDeep(props.schema));
        const { i18nState, i18n } = createI18n({ resource: { ...props.i18nResource } });

        provideTranslation({ i18n });
        provideRootId();
        const { eventBus } = provideEventBus();

        eventBus.$on('childFormChange', (event) => emit('childFormChange', event));
        eventBus.$on('formLink', (event) => emit('formLink', event));

        const validAdditionalErrors = computed(() => {
            return additionalErrors.value.filter((error) => {
                if (!isPlainObject(error) || !isString(error.instancePath)) {
                    console.error('Invalid additional error object: ', error);
                    return false;
                }
                return true;
            });
        });

        watch(
            () => props.schema,
            (newValue) => {
                // check if the new schema compiles correctly,
                // otherwise prevent the schema from being set.
                schemaError.value = validateSchema(newValue);
                if (!schemaError.value) {
                    localSchema.value = newValue;
                }
            },
            { deep: true }
        );

        function handleChange(event: FormChangeEvent) {
            if (!isEqual(data.value, event.data)) {
                emit('change', event);
            }
        }

        return {
            validAdditionalErrors,
            schemaError,
            data,
            localSchema: localSchema as Ref<CoreJsonSchema>,
            uiSchema: uiSchema as Ref<UISchemaElement>, // cast is needed because we customized the types
            cells: Object.freeze(props.cells), // freeze renderers for performance gains
            renderers: Object.freeze(props.renderers), // freeze renderers for performance gains
            handleChange,
            i18nState,
        };
    },
});
</script>
