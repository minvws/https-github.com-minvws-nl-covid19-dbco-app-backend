<template>
    <Card>
        <HStack class="tw-justify-between">
            <Heading size="xs" as="h3"
                >Dossier aanmaken voor
                <span class="tw-text-violet-700"
                    >{{ disease.name }}: version {{ disease.currentVersion }}</span
                ></Heading
            >
            <BDropdown
                :text="selectedTestFormId ? selectedTestFormId : 'test formulier data'"
                variant="outline-primary"
            >
                <BDropdownItem
                    v-for="testFormId in testFormIds"
                    :key="testFormId"
                    @click="onTestFormSelect(testFormId)"
                    >{{ testFormId }}</BDropdownItem
                >
            </BDropdown>
        </HStack>
        <JsonForms
            :class="{
                ['tw-opacity-50']: isCreatingDossier,
            }"
            @change="onChange"
            :readonly="isCreatingDossier"
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
        />
        <HStack class="tw-items-center">
            <Button :loading="isCreatingDossier" @click="createDossier">Create dossier</Button>
            <i v-if="lastCreatedDossier" class="tw-body-md tw-text-gray-600 tw-m-0">
                <Link
                    :href="`/playground?dossierId=${lastCreatedDossier.data.id}`"
                    target="_blank"
                    aria-label="Open het dossier in een nieuwe tab"
                    iconRight="external-link"
                >
                    Laatst aangemaakte dossier id: <strong>{{ lastCreatedDossier.data.id }}</strong>
                </Link>
            </i>
            <i v-else class="tw-body-sm tw-text-gray-400 tw-m-0">Log eerst in om een dossier aan te maken</i>
        </HStack>
    </Card>
</template>

<script lang="ts">
import { diseaseApi } from '@dbco/portal-api';
import type { DiseaseListItemDTO, DossierDTO } from '@dbco/portal-api/disease.dto';
import type { FormChangeEvent } from '@dbco/ui-library';
import { Link, HStack, Button, Card, Heading, JsonForms, Spinner } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { defineComponent, ref, toRef } from 'vue';
import { data as initialData, schema, uiSchema } from './createDossierForm';
import { testForms } from '../../../test-forms/test-forms';

export type TestFormId = keyof typeof testForms;

export default defineComponent({
    props: {
        disease: {
            type: Object as PropType<DiseaseListItemDTO>,
            required: true,
        },
    },
    components: {
        Button,
        Card,
        JsonForms,
        Heading,
        Spinner,
        HStack,
        Link,
    },
    emits: {
        created: () => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    setup(props, { emit }) {
        const isCreatingDossier = ref(false);
        const lastCreatedDossier = ref<DossierDTO | null>(null);
        const data = ref(initialData);
        const disease = toRef(props, 'disease');

        const selectedTestFormId = ref<TestFormId | null>(null);
        const testFormIds = ref(Object.keys(testForms) as TestFormId[]);

        const createDossier = async () => {
            if (!disease.value) return;

            isCreatingDossier.value = true;
            try {
                lastCreatedDossier.value = await diseaseApi.createDossier(
                    disease.value.id,
                    disease.value.currentVersion ?? 'current',
                    JSON.parse(data.value.data)
                );
            } catch (error) {
                console.error(error);
            }
            emit('created');
            isCreatingDossier.value = false;
        };

        const onChange = (event: FormChangeEvent) => {
            data.value = event.data;
        };

        const onTestFormSelect = (testFormId: TestFormId | undefined) => {
            if (!testFormId) return;
            const { data: testData } = testForms[testFormId];

            data.value = { data: JSON.stringify(testData, null, 2) };
            selectedTestFormId.value = testFormId;
        };

        return {
            createDossier,
            onChange,

            data,
            schema,
            uiSchema,

            isCreatingDossier,
            lastCreatedDossier,
            testFormIds,
            selectedTestFormId,
            onTestFormSelect,
        };
    },
});
</script>
