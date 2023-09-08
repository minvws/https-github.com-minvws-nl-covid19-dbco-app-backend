<template>
    <div>
        <AdminTabs />
        <Container>
            <VStack class="tw-mt-12" spacing="6">
                <div class="tw-flex tw-justify-between tw-items-end">
                    <Heading as="h2" size="sm">Versies</Heading>
                    <Button iconLeft="plus" size="sm" @click="open">Nieuwe beleidsversie</Button>
                </div>
                <TableContainer class="w-flex">
                    <Table>
                        <Thead>
                            <Tr>
                                <Th>Versienaam</Th>
                                <Th>Ingangsdatum</Th>
                                <Th>Status</Th>
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
                                v-for="version in versions"
                                :key="version.uuid"
                                @click="showVersionDetail(version.uuid)"
                                class="tw-cursor-pointer hover:tw-bg-violet-100"
                            >
                                <Td>{{ version.name }}</Td>
                                <Td>{{ $filters.dateFnsFormat(version.startDate, 'd MMMM yyyy') }}</Td>
                                <Td class="tw-flex tw-justify-between align-items-center">
                                    <PolicyVersionStatusBadge :status="version.status" />
                                    <Icon
                                        class="tw-w-3.5 tw-h-3.5 tw-text-violet-700 tw-ml-auto"
                                        name="chevron-right"
                                    />
                                </Td>
                            </Tr>
                        </Tbody>
                    </Table>
                </TableContainer>
            </VStack>
        </Container>
        <SaveModal
            okLabel="Aanmaken"
            title="Nieuw beleid"
            :loading="creationIsPending"
            :isOpen="isOpen"
            @close="close"
            @ok="createNewPolicy"
        >
            <FormulateForm v-model="newPolicy" class="form-container" :errors="createErrors">
                <FormulateInput
                    class="w100"
                    type="text"
                    name="name"
                    label="Versienaam (verplicht)"
                    :disabled="creationIsPending"
                    @change="clearErrors"
                />
                <FormulateInput
                    class="w100 tw-pb-6"
                    type="date"
                    name="startDate"
                    label="Ingangsdatum (verplicht)"
                    :disabled="creationIsPending"
                    :min="$filters.dateFnsFormat(new Date(), 'yyyy-MM-dd')"
                    @change="clearErrors"
                />
            </FormulateForm>
        </SaveModal>
    </div>
</template>

<script lang="ts" setup>
import PolicyVersionStatusBadge from '@/components/admin/PolicyAdvice/PolicyVersionStatusBadge/PolicyVersionStatusBadge.vue';
import AdminTabs from '@/components/admin/AdminTabs/AdminTabs.vue';
import { useRouter } from '@/router/router';
import useStatusAction, { isPending, isResolved } from '@/store/useStatusAction';
import {
    Button,
    Container,
    Heading,
    Icon,
    SaveModal,
    Spinner,
    Table,
    TableContainer,
    Tbody,
    Td,
    Th,
    Thead,
    Tr,
    useOpenState,
    VStack,
} from '@dbco/ui-library';
import { computed, onMounted, ref } from 'vue';
import { adminApi } from '@dbco/portal-api';
import type { PolicyVersion } from '@dbco/portal-api/admin.dto';
import type { AxiosError } from 'axios';

const { status, action: loadVersions } = useStatusAction(adminApi.getPolicyVersions);

onMounted(async () => {
    await loadVersions();
});

const versions = computed(() => {
    if (isResolved(status.value)) {
        return status.value.result;
    }
    return [];
});

const isLoading = computed(() => isPending(status.value));

async function showVersionDetail(uuid: string) {
    await useRouter().push(`/beheren/beleidsversies/${uuid}`);
}

function clearErrors() {
    createErrors.value = {};
}

// Create new Policy version
const newPolicy = ref<Pick<PolicyVersion, 'name' | 'startDate'> | undefined>(undefined);
const creationIsPending = ref(false);
const createErrors = ref<AnyObject>({});
const { isOpen, close, open } = useOpenState();

async function createNewPolicy() {
    creationIsPending.value = true;
    try {
        const createdPolicy = await adminApi.createPolicyVersion(newPolicy.value);
        createErrors.value = {};
        await useRouter().push(`/beheren/beleidsversies/${createdPolicy.uuid}`);
    } catch (error) {
        const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
        if (response?.data.errors) {
            const { name, startDate } = response.data.errors;
            if (name) createErrors.value = { name: JSON.stringify({ warning: name }) };
            if (startDate)
                createErrors.value = {
                    ...createErrors.value,
                    ...{ startDate: JSON.stringify({ warning: startDate }) },
                };
        }
    } finally {
        creationIsPending.value = false;
    }
}
</script>
