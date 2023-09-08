<template>
    <Container>
        <TableContainer>
            <Table>
                <Thead>
                    <Tr>
                        <Th>ID</Th>
                        <Th>Status</Th>
                        <Th>Version</Th>
                        <Th></Th>
                    </Tr>
                </Thead>
                <TBody v-if="isLoading">
                    <Tr>
                        <Td colspan="3" class="tw-p-4 tw-text-center tw-w-full">
                            <Spinner size="lg" class="tw-inline-block" />
                        </Td>
                    </Tr>
                </TBody>
                <Tbody v-else>
                    <Tr
                        v-for="(model, index) in models"
                        :class="{ 'tw-bg-blue-50': model.id === selectedId }"
                        :key="`schema-${model.id}`"
                    >
                        <Td>{{ model.id }}</Td>
                        <Td>{{ model.status }}</Td>
                        <Td>{{ model.version }}</Td>
                        <Td>
                            <Button @click="onSelect(index)">{{
                                model.id !== selectedId ? 'Select' : 'Deselect'
                            }}</Button>
                            <Button @click="onDelete(index)" :disabled="model.status !== VersionStatus.Draft"
                                >Delete</Button
                            >
                            <Button @click="onPublish(index)" :disabled="model.status !== VersionStatus.Draft"
                                >Publish</Button
                            >
                            <Button @click="onArchive(index)" :disabled="model.status !== VersionStatus.Published">
                                Archive
                            </Button>
                            <Button @click="onClone(index)" :disabled="hasDraft">Clone</Button>
                        </Td>
                    </Tr>
                </Tbody>
            </Table>
        </TableContainer>

        <Card>
            <JsonForms @change="onChange" :data="crudData" :schema="schema" :uiSchema="uiSchema" />
            <Button @click="onSubmit">{{ selectedId ? 'Wijzig' : 'Aanmaken' }}</Button>
        </Card>
    </Container>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, ref, watch } from 'vue';
import {
    JsonForms,
    Button,
    Card,
    Container,
    Spinner,
    TableContainer,
    TableCaption,
    Table,
    Thead,
    Th,
    Tr,
    Td,
    Tbody,
    Tfoot,
} from '@dbco/ui-library';
import { diseaseApi } from '@dbco/portal-api';
import { data as defaultData, schema, uiSchema } from './diseaseSchemasForm';
import type { FormChangeEvent } from '@dbco/ui-library';
import { VersionStatus } from '@dbco/portal-api/disease.dto';
import type { DiseaseModelListItemDTO } from '@dbco/portal-api/disease.dto';

export default defineComponent({
    components: {
        Container,
        Button,
        Card,
        JsonForms,
        Spinner,
        TableContainer,
        TableCaption,
        Table,
        Thead,
        Td,
        Th,
        Tr,
        Tbody,
        Tfoot,
    },
    props: {
        diseaseId: {
            type: Number,
            required: true,
        },
    },
    setup(props, { emit }) {
        const crudData = ref(defaultData);
        const isLoading = ref(true);
        const models = ref<DiseaseModelListItemDTO[] | null>(null);
        const selectedId = ref<number | null>(null);

        const load = async () => {
            isLoading.value = true;
            models.value = await diseaseApi.listDiseaseModels(props.diseaseId);
            if (!selectedId.value && models.value.length) {
                onSelect(0);
            }
            isLoading.value = false;
        };

        onMounted(load);
        watch(
            () => props.diseaseId,
            () => {
                selectedId.value = null;
                crudData.value = { ...defaultData };
                emit('select', null);
                void load();
            }
        );

        const hasDraft = computed(() => models.value?.some((model) => model.status === VersionStatus.Draft) ?? false);

        const onChange = (event: FormChangeEvent) => {
            crudData.value = event.data;
        };

        const onSelect = async (index: number) => {
            if (models.value === null) return;

            const diseaseModel = await diseaseApi.getDiseaseModel(models.value[index].id);
            const { id, dossierSchema, contactSchema, eventSchema, sharedDefs } = diseaseModel.data;

            if (selectedId.value === id) {
                selectedId.value = null;
                crudData.value = { ...defaultData };
                emit('select', null);
                return;
            }

            selectedId.value = id;
            crudData.value = { dossierSchema, contactSchema, eventSchema, sharedDefs: sharedDefs ?? '' };
            emit('select', id);
        };

        const onDelete = async (index: number) => {
            if (models.value === null) return;
            if (!confirm('Are you sure?')) return;

            await diseaseApi.deleteDiseaseModel(models.value[index].id);

            void load();
        };

        const onSubmit = async () => {
            selectedId.value !== null
                ? await diseaseApi.updateDiseaseModel(selectedId.value, crudData.value)
                : await diseaseApi.createDiseaseModel(props.diseaseId, crudData.value);

            void load();
        };

        const onArchive = async (index: number) => {
            if (models.value === null) return;

            await diseaseApi.archiveDiseaseModel(models.value[index].id);
            void load();
        };

        const onClone = async (index: number) => {
            if (models.value === null) return;

            await diseaseApi.cloneDiseaseModel(models.value[index].id);
            void load();
        };

        const onPublish = async (index: number) => {
            if (models.value === null) return;

            await diseaseApi.publishDiseaseModel(models.value[index].id);
            void load();
        };

        return {
            models,
            onArchive,
            onChange,
            onClone,
            onDelete,
            onPublish,
            onSelect,
            onSubmit,
            selectedId,

            crudData,
            schema,
            uiSchema,

            hasDraft,
            VersionStatus,
            isLoading,
        };
    },
});
</script>
