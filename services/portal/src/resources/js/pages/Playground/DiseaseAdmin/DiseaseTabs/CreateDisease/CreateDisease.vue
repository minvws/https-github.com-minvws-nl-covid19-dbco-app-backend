<template>
    <Card>
        <Heading size="xs" as="h3">Disease</Heading>
        <JsonForms
            @change="onChange"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            :class="{
                ['tw-opacity-50']: isLoading,
            }"
        />
        <HStack class="tw-items-center">
            <Button :loading="isLoading" @click="onSubmit">{{ disease ? 'Wijzig' : 'Aanmaken' }}</Button>
            <span class="tw-body-sm tw-text-gray-500">{{ logMessage }}</span>
        </HStack>
    </Card>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { watch, toRef, defineComponent, ref } from 'vue';
import type { FormChangeEvent } from '@dbco/ui-library';
import { HStack, JsonForms, Button, Card, Heading, Spinner } from '@dbco/ui-library';
import { diseaseApi } from '@dbco/portal-api';
import { data as defaultData, schema, uiSchema } from './diseasesForm';
import { schema as dossierSchema, uiSchema as dossierUiSchema } from '../CreateDossier/createDossierForm';
import type {
    DiseaseCreateUpdateResponseDTO,
    DiseaseListItemDTO,
    DiseaseModelCreateUpdateRequestDTO,
    DiseaseModelDetail,
} from '@dbco/portal-api/disease.dto';
import type { TestForm } from '../../../test-forms/test-forms';
import { testForms } from '../../../test-forms/test-forms';

const toJson = (value: any) => JSON.stringify(value, null, 2);

export default defineComponent({
    components: {
        Button,
        Card,
        JsonForms,
        Heading,
        Spinner,
        HStack,
    },
    emits: {
        createOrUpdate: (diseaseId: number) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        disease: {
            type: Object as PropType<DiseaseListItemDTO>,
        },
    },
    setup(props, { emit }) {
        const isLoading = ref(false);
        const data = ref(defaultData);
        const logMessage = ref('');
        const disease = toRef(props, 'disease');

        const onChange = (event: FormChangeEvent) => {
            data.value = event.data;
        };

        function log(message: string) {
            logMessage.value = message;
            console.warn(message);
        }

        watch(
            () => props.disease,
            (newDisease) => {
                if (newDisease) {
                    data.value = { name: newDisease.name, code: newDisease.code, testForm: '' };
                } else {
                    data.value = { ...defaultData };
                }
            },
            { deep: true }
        );

        const onSubmit = async () => {
            const { code, name, testForm } = data.value;
            isLoading.value = true;

            log(disease.value ? `updating disease..` : 'creating new disease...');
            const newDisease = disease.value
                ? await diseaseApi.updateDisease(disease.value.id, data.value)
                : await diseaseApi.createDisease({ code, name });

            if (testForm) {
                log(`adding test form "${testForm}" to "${newDisease.data.name}" ...`);
                const diseaseModel = await addDiseaseSchemas(newDisease, testForms[testForm]);
                await addDiseaseUiSchemas(diseaseModel, testForms[testForm]);
            }
            log(`done!`);

            isLoading.value = false;
            emit('createOrUpdate', newDisease.data.id);
        };

        async function addDiseaseSchemas(disease: DiseaseCreateUpdateResponseDTO, testForm: TestForm) {
            const { schema, contactSchema, eventSchema } = testForm;

            const request: DiseaseModelCreateUpdateRequestDTO = {
                dossierSchema: toJson(schema),
                contactSchema: contactSchema ? toJson(contactSchema) : '{}',
                eventSchema: eventSchema ? toJson(eventSchema) : '{}',
                sharedDefs: '[]',
            };

            log('adding disease schemas...');
            const { data: diseaseModel } = await diseaseApi.createDiseaseModel(disease.data.id, request);

            log('publishing disease schemas...');
            await diseaseApi.publishDiseaseModel(diseaseModel.id);

            return diseaseModel;
        }

        async function addDiseaseUiSchemas(diseaseModel: DiseaseModelDetail, testForm: TestForm) {
            const { uiSchema, contactUiSchema, eventUiSchema } = testForm;
            const request: DiseaseModelCreateUpdateRequestDTO = {
                dossierSchema: toJson(uiSchema),
                contactSchema: contactUiSchema ? toJson(contactUiSchema) : '{}',
                eventSchema: eventUiSchema ? toJson(eventUiSchema) : '{}',
            };

            log('adding disease UI schemas...');
            const { data: diseaseUiModel } = await diseaseApi.createDiseaseUIModel(diseaseModel.id, request);

            log('publishing disease UI schemas...');
            await diseaseApi.publishDiseaseUIModel(diseaseUiModel.id);
        }

        return {
            onChange,
            onSubmit,

            data,
            schema,
            uiSchema,

            dossierSchema,
            dossierUiSchema,

            isLoading,
            logMessage,
        };
    },
});
</script>
