<template>
    <div v-if="!loadingDossier && data && schema && uiSchema">
        <DbcoForm :initialData="data" :schema="schema" :uiSchema="uiSchema" />

        <JsonFormsEditor v-if="debug" :data="data" :schema="schema" :uiSchema="uiSchema" @change="handleEditorChange" />
    </div>
    <div v-else class="tw-p-8 tw-text-center">
        <span v-if="loadingDossierError">
            Er ging iets mis tijdens het laden van het dossier: <br /><br />
            <code class="tw-text-red-700">{{ loadingDossierError }}</code>
        </span>
        <span v-else>
            <Spinner size="lg" class="tw-inline-block" />
        </span>
    </div>
</template>

<script lang="ts">
import DbcoForm from '@/components/json-forms/DbcoForm/DbcoForm.vue';
import { diseaseApi } from '@dbco/portal-api';
import { Entity } from '@dbco/portal-api/disease.dto';
import type { FormRootData, JsonFormsEditorChangeEvent, JsonSchema, UiSchema } from '@dbco/ui-library';
import { Button, Container, HStack, Spinner, JsonFormsEditor } from '@dbco/ui-library';
import type { Ref } from 'vue';
import { defineComponent, onMounted, ref } from 'vue';
import { testForms } from '../../test-forms/test-forms';

export default defineComponent({
    name: 'Dossier',
    components: {
        Container,
        Button,
        HStack,
        DbcoForm,
        Spinner,
        JsonFormsEditor,
    },
    props: {
        debug: Boolean,
        dossierId: {
            type: Number,
        },
        testFormId: {
            type: String,
        },
    },
    setup({ dossierId, testFormId }) {
        const loadingDossierError = ref('');
        const loadingDossier = ref(true);

        const data = ref<FormRootData | null>(null);
        const schema = ref<JsonSchema | null>(null);
        const uiSchema = ref(null) as Ref<UiSchema | null>;

        const loadDossier = async () => {
            if (!dossierId) return;
            loadingDossier.value = true;
            loadingDossierError.value = '';

            try {
                const dossier = await diseaseApi.getDossier(dossierId);
                data.value = dossier as any;

                const form = await diseaseApi.getForm(
                    dossier.data.diseaseModel.disease.id,
                    dossier.data.diseaseModel.version,
                    Entity.Dossier
                );

                schema.value = form.dataSchema;
                uiSchema.value = form.uiSchema as UiSchema;
            } catch (error) {
                loadingDossierError.value = (error as any).message;
            }

            loadingDossier.value = false;
        };

        const loadTestForm = () => {
            if (!testFormId) return;

            loadingDossierError.value = '';
            loadingDossier.value = false;

            const {
                data: testData,
                schema: testSchema,
                uiSchema: testUiSchema,
                dataFE: testDataFE,
                schemaFE: testSchemaFE,
            } = testForms[testFormId];

            // wrap data and schema inside data property, this would normally be done by the BE
            data.value = { ...(testDataFE ? testDataFE : testData), $links: {}, $config: '' };
            schema.value = {
                type: 'object',
                required: ['data'],
                properties: { data: testSchemaFE ? testSchemaFE : (testSchema as any) },
            };
            uiSchema.value = testUiSchema;
        };

        const handleEditorChange = ({
            schema: newSchema,
            data: newData,
            uiSchema: newUiSchema,
        }: JsonFormsEditorChangeEvent) => {
            if (newSchema) schema.value = newSchema;
            if (newData) data.value = newData;
            if (newUiSchema) uiSchema.value = newUiSchema as UiSchema;
        };

        onMounted(() => {
            if (dossierId) {
                void loadDossier();
            } else if (testFormId) {
                loadTestForm();
            }
        });

        return {
            dossierId,
            loadingDossier,
            loadingDossierError,
            handleEditorChange,

            data,
            schema,
            uiSchema,
        };
    },
});
</script>

<style lang="scss" scoped>
::v-deep {
    .tab-content {
        padding: 3rem;
    }

    .group {
        background-color: #ffffff;
        border-radius: 0.25rem;
        border: 1px solid #e6e6ef;
        box-shadow:
            0px 1px 0px #e6e6ef,
            0px 2px 4px rgb(0 0 0 / 3%);
        margin-bottom: 1rem;
        padding: 1.5rem 2rem;
        position: relative;

        &-label {
            color: #001e49;
            font-size: 1.25rem;
            font-weight: bold;
            letter-spacing: 0.15px;
            margin: -2rem;
            padding-bottom: 2.5rem;
        }
    }

    input,
    select,
    textarea {
        border-radius: 0.3em;
        border: 1px solid #cecece;
        font-weight: 400;
        line-height: 1.2em;
        padding: 0.75rem 1rem;
        width: 100%;
    }

    button {
        color: #ffffff;
        background-color: #5616ff;
        border: 1px solid #4200ef;
        border-radius: 0.25rem;
        font-size: 1rem;
        margin: 0 0.125rem;
        padding: 0.375rem 0.75rem;

        transition: all 0.15s ease-in-out;

        &:hover {
            background-color: #3e00e2;
            border-color: #6830ff;
        }
    }

    .vertical-layout {
    }

    .horizontal-layout {
        gap: 1rem;
    }

    .b-dropdown button {
        font-size: 1rem !important;
    }
}
</style>
