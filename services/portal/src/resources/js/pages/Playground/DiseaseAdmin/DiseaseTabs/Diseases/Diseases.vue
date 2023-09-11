<template>
    <Container>
        <TableContainer class="tw-mb-3">
            <Table>
                <Thead>
                    <Tr>
                        <Th>ID</Th>
                        <Th>Diseases</Th>
                        <Th>Code</Th>
                        <Th>CurrentVersion</Th>
                        <Th>Active</Th>
                        <Th></Th>
                    </Tr>
                </Thead>
                <TBody v-if="isLoading">
                    <Tr>
                        <Td colspan="5" class="tw-p-4 tw-text-center tw-w-full">
                            <Spinner size="lg" class="tw-inline-block" />
                        </Td>
                    </Tr>
                </TBody>
                <Tbody v-else>
                    <Tr
                        v-for="(disease, index) in diseases"
                        :class="{ 'tw-bg-blue-50': disease.id === selectedId }"
                        :key="`disease-${disease.id}`"
                    >
                        <Td>{{ disease.id }}</Td>
                        <Td>{{ disease.name }}</Td>
                        <Td>{{ disease.code }}</Td>
                        <Td>{{ disease.currentVersion }}</Td>
                        <Td>{{ disease.isActive }}</Td>
                        <Td>
                            <Button @click="onSelect(index)">{{
                                disease.id !== selectedId ? 'Select' : 'Deselect'
                            }}</Button>
                            <Button @click="onDelete(index)">Delete</Button>
                        </Td>
                    </Tr>
                </Tbody>
            </Table>
        </TableContainer>

        <CreateDisease :disease="selectedDisease" @createOrUpdate="handleDiseaseCreatedOrUpdated" />
        <CreateDossier v-if="selectedDisease" :disease="selectedDisease" />
    </Container>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, ref } from 'vue';
import {
    JsonForms,
    Button,
    Card,
    Container,
    Heading,
    Spinner,
    TableContainer,
    Table,
    Thead,
    Th,
    Tr,
    Td,
    Tbody,
} from '@dbco/ui-library';
import { diseaseApi } from '@dbco/portal-api';
import CreateDisease from '../CreateDisease/CreateDisease.vue';
import CreateDossier from '../CreateDossier/CreateDossier.vue';
import type { DiseaseListItemDTO } from '@dbco/portal-api/disease.dto';

export default defineComponent({
    name: 'Diseases',
    components: {
        Container,
        Button,
        Card,
        JsonForms,
        Heading,
        Spinner,
        TableContainer,
        Table,
        Thead,
        Td,
        Th,
        Tr,
        Tbody,
        CreateDisease,
        CreateDossier,
    },
    setup(_, { emit }) {
        const isLoading = ref(true);
        const diseases = ref<DiseaseListItemDTO[] | null>(null);

        const selectedId = ref<number | null>(null);
        const selectedDisease = computed(() => diseases.value?.find((disease) => disease.id === selectedId.value));

        const load = async () => {
            isLoading.value = true;
            diseases.value = await diseaseApi.listDiseases();
            isLoading.value = false;
        };

        onMounted(load);

        const onSelect = (index: number) => {
            if (diseases.value === null) return;
            const { id } = diseases.value[index];

            selectedId.value = selectedId.value === id ? null : id;
            emit('select', selectedId.value);
        };

        const onDelete = async (index: number) => {
            if (diseases.value === null) return;
            if (!confirm('Are you sure?')) return;

            await diseaseApi.deleteDisease(diseases.value[index].id);

            void load();
        };

        async function handleDiseaseCreatedOrUpdated(diseaseId: number) {
            await load();
            if (selectedId.value !== diseaseId) {
                selectedId.value = diseaseId;
                emit('select', selectedId.value);
            }
        }

        return {
            diseases,
            onDelete,
            onSelect,
            selectedId,
            selectedDisease,

            handleDiseaseCreatedOrUpdated,
            load,
            isLoading,
        };
    },
});
</script>
