<template>
    <JsonFormsBase
        :data="data"
        :schema="schema"
        :uiSchema="uiSchema"
        :i18nResource="i18nResource"
        :additionalErrors="allAdditionalErrors"
        v-on="{ ...$listeners, change: handleFormChange }"
    />
</template>

<script lang="ts">
import { computed, defineComponent, toRef } from 'vue';
import type { FormChangeEvent, FormError, FormMetaData } from '../../types';
import JsonFormsBase from '../JsonFormsBase/JsonFormsBase.vue';
import { props } from './props';
import { emits } from './emits';
import { injectFormActionHandler } from '../JsonForms/provide';

export default defineComponent({
    props,
    emits,
    components: {
        JsonFormsBase,
    },
    setup(props, { emit }) {
        const data = toRef(props, 'data');
        const schema = toRef(props, 'schema');
        const additionalErrors = toRef(props, 'additionalErrors');
        const { formActionHandler } = injectFormActionHandler();

        const $links = computed(() => (data.value as FormMetaData).$links);
        const allAdditionalErrors = computed(() => {
            const { $validationErrors } = data.value as FormMetaData;
            return [...($validationErrors || []), ...(additionalErrors.value || [])] as FormError[];
        });

        if ((!$links.value && formActionHandler) || ($links.value && !formActionHandler)) {
            console.error(
                'The \`formActionHandler\` works in combination with the \`$links\` meta data. You should not only provide one of them. Most likely you forgot to set the \`actionHandler\` on the \`JsonForms\` component.'
            );
        }

        async function handleFormChange(event: FormChangeEvent) {
            if (formActionHandler) {
                await updateAsyncFormData(event);
            } else {
                emit('change', event);
            }
        }

        async function updateAsyncFormData(event: FormChangeEvent) {
            /* c8 ignore next 6 */
            if (!formActionHandler || !$links.value.update) {
                return console.error(
                    'No action handler found - or - no update link is available in the meta data of this form.',
                    data.value
                );
            }

            const newData = await formActionHandler.update($links.value.update, event.data);
            if (!newData) {
                throw new Error('No data received from the form action update handler!');
            }
            event.data = newData;
            emit('change', event);
        }

        return { data, schema, allAdditionalErrors, handleFormChange };
    },
});
</script>
