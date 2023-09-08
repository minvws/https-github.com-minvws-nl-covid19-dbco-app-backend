<template>
    <div>
        <Spinner v-if="isLoadingForm" size="lg" class="tw-inline-block" />
        <DbcoForm
            v-else-if="initialData && schema && uiSchema"
            :initialData="initialData"
            :schema="schema"
            :uiSchema="uiSchema"
        />
        <div v-else>Er ging iets mis tijdens het laden van het formulier.</div>
    </div>
</template>

<script lang="ts">
import { getAxiosInstance } from '@dbco/portal-api/defaults';
import type { JsonSchema, UiSchema, FormData, FormRootData } from '@dbco/ui-library';
import { Spinner } from '@dbco/ui-library';
import type { DefineComponent, Ref } from 'vue';
import { defineComponent, onMounted, ref } from 'vue';

export default defineComponent({
    components: {
        DbcoForm: (() => {
            return import('./DbcoForm.vue').then((x) => x.default);
        }) as unknown as DefineComponent,
        Spinner,
    },
    props: {
        dataHref: { type: String, required: true },
    },
    setup({ dataHref }) {
        const isLoadingForm = ref(true);
        const initialData = ref<FormData | null>(null);
        const schema = ref<JsonSchema | null>(null);
        const uiSchema = ref(null) as Ref<UiSchema | null>;

        onMounted(() => {
            void loadForm();
        });

        async function loadForm() {
            isLoadingForm.value = true;
            initialData.value = null;
            schema.value = null;
            uiSchema.value = null;

            const axios = getAxiosInstance();
            const { data } = await axios.get<FormRootData>(dataHref);

            if (!data.$config) {
                console.error('No $config found in the FormData!');
                return;
            }
            const { data: schemas } = await axios.get(data.$config);
            if (!schemas || !schemas.dataSchema || !schemas.uiSchema) {
                console.error('No schemas found in the form link!', data.$config);
                return;
            }

            initialData.value = data;
            schema.value = schemas.dataSchema;
            uiSchema.value = schemas.uiSchema;

            isLoadingForm.value = false;
        }

        return { initialData, schema, uiSchema, isLoadingForm };
    },
});
</script>
