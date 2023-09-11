<template>
    <Table>
        <Thead>
            <Tr>
                <Th>Variant</Th>
            </Tr>
        </Thead>
        <Tbody v-if="guidelines.length">
            <Tr
                @click="showGuidelineDetail(guideline.uuid)"
                class="tw-cursor-pointer hover:tw-bg-violet-100"
                v-for="guideline in guidelines"
                :key="guideline.uuid"
            >
                <Td :data-testid="guideline.name" class="tw-flex tw-justify-between align-items-center">
                    {{ guideline.name }}
                    <Icon class="tw-w-3.5 tw-h-3.5 tw-text-violet-700" name="chevron-right" />
                </Td>
            </Tr>
        </Tbody>
        <TBody v-else>
            <Tr>
                <Td class="tw-p-4 tw-text-center tw-w-full">
                    <span>Geen richtlijnen gevonden</span>
                </Td>
            </Tr>
        </TBody>
    </Table>
</template>

<script lang="ts" setup>
import { useRoute, useRouter } from '@/router/router';
import type { PolicyGuideline } from '@dbco/portal-api/admin.dto';
import { Icon, Table, Tbody, Td, Th, Thead, Tr } from '@dbco/ui-library';
import type { PropType } from 'vue';

defineProps({
    guidelines: { type: Array as PropType<PolicyGuideline[]>, required: true },
});

const { versionUuid } = useRoute().params;

async function showGuidelineDetail(guidelineUuid: string) {
    await useRouter().push(`/beheren/beleidsversies/${versionUuid}/richtlijnen/${guidelineUuid}`);
}
</script>
