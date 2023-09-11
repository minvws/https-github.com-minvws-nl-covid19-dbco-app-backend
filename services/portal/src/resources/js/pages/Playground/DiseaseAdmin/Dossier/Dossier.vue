<template>
    <Container>
        <Stack direction="column">
            <HStack class="tw-mt-5">
                <input
                    aria-label="dossier"
                    type="text"
                    id="dossier-id"
                    placeholder="Dossier ID"
                    class="tw-max-w-[160px]"
                    v-model="dossierId"
                    @change="handleDossierIdChange"
                />
                <Button :disabled="!dossierId" :loading="loadingDossier" @click="loadDossier">Laad dossier</Button>
                <Button
                    :href="`/playground?dossierId=${dossierId}`"
                    target="_blank"
                    aria-label="Open dossier in een nieuwe tab"
                    :disabled="!dossierId"
                    >Open dossier</Button
                >
            </HStack>
            <HStack class="tw-mt-5">
                <BDropdown
                    :text="selectedTestFormId ? selectedTestFormId : 'Selecteer een formulier'"
                    variant="outline-primary"
                >
                    <BDropdownItem
                        v-for="testFormId in testFormIds"
                        :key="testFormId"
                        @click="handleTestFormSelect(testFormId)"
                        >{{ testFormId }}</BDropdownItem
                    >
                </BDropdown>
                <Button :disabled="!selectedTestFormId" @click="loadTestForm">Laad test formulier</Button>
                <Button
                    :href="`/playground?testFormId=${selectedTestFormId}`"
                    target="_blank"
                    aria-label="Open test formulier als dossier"
                    :disabled="!selectedTestFormId"
                    >Open test formulier</Button
                >
            </HStack>
        </Stack>

        <hr />

        <div>
            <div v-if="!loadingDossier && data && schema && uiSchema">
                <DbcoForm
                    class="tw-bg-gray-200/30 tw-py-8 tw-px-2"
                    :initialData="data"
                    :schema="schema"
                    :uiSchema="uiSchema"
                />

                <hr />

                <JsonFormsEditor
                    v-if="debug"
                    :data="data"
                    :schema="schema"
                    :uiSchema="uiSchema"
                    @change="handleEditorChange"
                />
            </div>
            <div v-else class="tw-border tw-border-dashed tw-border-gray-400 tw-p-8 tw-text-center">
                <span v-if="loadingDossier">
                    <Spinner size="lg" class="tw-inline-block" />
                </span>
                <span v-else-if="loadingDossierError">
                    Er ging iets mis tijdens het laden van het dossier: <br /><br />
                    <code class="tw-text-red-700">{{ loadingDossierError }}</code>
                </span>
                <p v-else>Laad een dossier of een test formulier</p>
            </div>
        </div>
    </Container>
</template>

<script lang="ts">
import DbcoForm from '@/components/json-forms/DbcoForm/DbcoForm.vue';
import { diseaseApi } from '@dbco/portal-api';
import { Entity } from '@dbco/portal-api/disease.dto';
import type { FormRootData, JsonFormsEditorChangeEvent, JsonSchema, UiSchema } from '@dbco/ui-library';
import { Button, Container, HStack, JsonFormsEditor, Spinner, Stack } from '@dbco/ui-library';
import type { Ref } from 'vue';
import { defineComponent, ref } from 'vue';
import { testForms } from '../../test-forms/test-forms';

type TestFormId = keyof typeof testForms;

export default defineComponent({
    name: 'Dossier',
    components: {
        Container,
        Button,
        Stack,
        HStack,
        DbcoForm,
        Spinner,
        JsonFormsEditor,
    },
    props: {
        debug: Boolean,
    },
    setup() {
        const loadingDossierError = ref('');
        const loadingDossier = ref(false);
        const dossierId = ref<number>();

        const data = ref<FormRootData | null>(null);
        const schema = ref<JsonSchema | null>(null);
        const uiSchema = ref(null) as Ref<UiSchema | null>;
        const validationErrors = ref();
        const selectedTestFormId = ref<TestFormId | null>(null);
        const testFormIds = ref(Object.keys(testForms) as TestFormId[]);

        const handleTestFormSelect = (testFormId: TestFormId) => {
            selectedTestFormId.value = testFormId;
            dossierId.value = undefined;
        };
        const handleDossierIdChange = () => {
            if (dossierId.value) {
                selectedTestFormId.value = null;
            }
        };

        const loadDossier = async () => {
            if (!dossierId.value || loadingDossier.value) return;
            selectedTestFormId.value = null;
            loadingDossier.value = true;
            loadingDossierError.value = '';

            try {
                const dossier = await diseaseApi.getDossier(dossierId.value);
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
            if (!selectedTestFormId.value) return;

            dossierId.value = undefined;
            loadingDossierError.value = '';
            loadingDossier.value = false;

            const {
                data: testData,
                schema: testSchema,
                uiSchema: testUiSchema,
                dataFE: testDataFE,
                schemaFE: testSchemaFE,
            } = testForms[selectedTestFormId.value];

            // wrap data and schema inside data property, this would normally be done by the BE
            data.value = { ...(testDataFE ? testDataFE : testData), $links: {}, $config: '' };
            schema.value = testSchemaFE ? testSchemaFE : (testSchema as any);
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

        return {
            dossierId,
            testFormIds,
            selectedTestFormId,
            loadingDossier,
            handleEditorChange,

            data,
            schema,
            uiSchema,
            validationErrors,
            handleTestFormSelect,
            handleDossierIdChange,
            loadTestForm,
            loadDossier,
            loadingDossierError,
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
