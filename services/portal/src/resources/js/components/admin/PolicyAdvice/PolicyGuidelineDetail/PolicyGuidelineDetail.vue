<template>
    <FullScreenModal :path="['Richtlijnen', guideline?.name]" @onClose="useRouter().back()">
        <template v-slot:header>
            <p class="tw-mb-0 tw-text-lg">
                <span>Beheren / </span>
                <strong>Beheermodule kalender index</strong>
            </p>
        </template>
        <template v-slot:title>
            <PolicyVersionStatusBadge
                :status="guideline?.policyVersionStatus ?? PolicyVersionStatusV1.VALUE_draft"
                class="tw-ml-4"
            />
        </template>
        <template v-slot:action>
            <div class="policy-guideline-modal-action tw-flex tw-gap-5 tw-items-baseline">
                <LastUpdated />
                <Button size="sm" @click="useRouter().back()">Terug naar beleid</Button>
            </div>
        </template>
        <div class="tw-min-h-screen">
            <Container>
                <Spinner v-if="loadPending" size="lg" />
                <VStack v-else-if="guideline?.uuid.length">
                    <CalendarItemConfigCardWrapper
                        v-for="config in calendarItemConfigs"
                        :config="config"
                        :key="config.uuid"
                        :versionStatus="guideline.policyVersionStatus"
                    />
                </VStack>
                <span v-else>Geen richtlijn gevonden</span>
            </Container>
        </div>
    </FullScreenModal>
</template>

<script lang="ts" setup>
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import LastUpdated from '@/components/caseEditor/LastUpdated/LastUpdated.vue';
import PolicyVersionStatusBadge from '@/components/admin/PolicyAdvice/PolicyVersionStatusBadge/PolicyVersionStatusBadge.vue';
import useStatusAction from '@/store/useStatusAction';
import { adminApi } from '@dbco/portal-api';
import { Button, Container, Spinner, VStack } from '@dbco/ui-library';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useRoute, useRouter } from '@/router/router';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import type { CalendarItemConfig } from '@dbco/portal-api/admin.dto';
import CalendarItemConfigCardWrapper from '@/components/admin/PolicyAdvice/CalendarItemConfig/CalendarItemConfigCardWrapper.vue';
import { useEventBus } from '@/composables/useEventBus';

const { versionUuid, policyGuidelineUuid } = useRoute().params;
const {
    action: loadGuideline,
    isPending: loadPending,
    result: guideline,
} = useStatusAction(adminApi.getPolicyGuideline);

onMounted(() => {
    useEventBus().$on('policy-status-change', setGuideline);
    void setGuideline();
});

onBeforeUnmount(() => {
    useEventBus().$off('policy-status-change', setGuideline);
});

async function setGuideline() {
    await loadGuideline(versionUuid, policyGuidelineUuid);
    if (guideline.value?.uuid) {
        void loadConfigs();
    }
}

const calendarItemConfigs = ref<CalendarItemConfig[]>([]);
async function loadConfigs() {
    calendarItemConfigs.value = await adminApi.getCalendarItemConfigs(policyGuidelineUuid, versionUuid);
}
</script>
