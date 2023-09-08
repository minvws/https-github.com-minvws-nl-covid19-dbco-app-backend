<!-- eslint-disable vuejs-accessibility/no-onchange, vuejs-accessibility/form-control-has-label -->
<template>
    <Table>
        <Thead>
            <Tr>
                <Th>Type</Th>
                <Th>Richtlijnen</Th>
            </Tr>
        </Thead>
        <Tbody v-if="riskProfiles.length">
            <Tr v-for="profile in riskProfiles" :key="profile.uuid">
                <Td class="tw-flex align-items-center tw-text-left">
                    {{ profile.name }}
                    <RiskProfileTooltip :riskProfileEnum="profile.riskProfileEnum"></RiskProfileTooltip>
                </Td>
                <Td class="tw-p-0">
                    <Select
                        size="sm"
                        variant="plain"
                        :disabled="disabled"
                        :invalid="errors.includes(profile.uuid)"
                        @change="handleInput(profile.policyVersionUuid, profile.uuid, $event)"
                    >
                        <option
                            v-for="guideline in guidelines"
                            :key="guideline.uuid"
                            :value="guideline.uuid"
                            :selected="guideline.uuid === profile.policyGuidelineUuid"
                        >
                            {{ guideline.name }}
                        </option>
                    </Select>
                </Td>
            </Tr>
        </Tbody>
        <TBody v-else>
            <Tr>
                <Td colspan="2" class="tw-p-4 tw-text-center tw-w-full">
                    <span>Geen risicoprofielen gevonden</span>
                </Td>
            </Tr>
        </TBody>
        <SaveModal
            cancelIsPrimary
            :isOpen="isOpen"
            title="Status terugzetten naar concept om je wijzigingen op te slaan?"
            @close="close"
            okLabel="Terugzetten"
            @ok="handlePendingUpdates"
        >
            <p class="tw-mb-0">
                Je moet dit beleid eerst terugzetten naar concept om wijzigingen op te slaan. Let op dat het beleid dan
                niet meer actief wordt op de ingangsdatum, tenzij je het opnieuw klaarzet.
            </p>
        </SaveModal>
    </Table>
</template>

<script lang="ts" setup>
import showToast from '@/utils/showToast';
import RiskProfileTooltip from './RiskProfileTooltip.vue';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import { adminApi } from '@dbco/portal-api';
import type { PolicyGuideline, RiskProfile } from '@dbco/portal-api/admin.dto';
import { Table, Tbody, Td, Th, Thead, Tr, SaveModal, Select, useOpenState } from '@dbco/ui-library';
import { ref, type PropType } from 'vue';

const emit = defineEmits(['status-changed', 'reset']);
const props = defineProps({
    disabled: { type: Boolean, default: false, required: false },
    riskProfiles: { type: Array as PropType<RiskProfile[]>, required: true },
    versionStatus: { type: String as PropType<PolicyVersionStatusV1>, required: true },
    guidelines: { type: Array as PropType<PolicyGuideline[]>, required: true },
});

const errors = ref<string[]>([]);
const { isOpen, close, open } = useOpenState({ onClose: () => emit('reset') });

type PendingUpdate = {
    versionUuid: string;
    variantUuid: string;
    inputEvent: FocusEvent<HTMLSelectElement>;
};

const pendingUpdate = ref<PendingUpdate>({
    versionUuid: '',
    variantUuid: '',
    inputEvent: {} as FocusEvent<HTMLSelectElement>,
});

const handleInput = (versionUuid: string, variantUuid: string, event: FocusEvent<HTMLSelectElement>) => {
    if (props.versionStatus !== PolicyVersionStatusV1.VALUE_active_soon) {
        return updateGuideline(versionUuid, variantUuid, event);
    }
    pendingUpdate.value = {
        versionUuid,
        variantUuid,
        inputEvent: event,
    };
    open();
};

const handlePendingUpdates = async () => {
    const updatedVersion = await adminApi.updatePolicyVersion(pendingUpdate.value.versionUuid, {
        status: PolicyVersionStatusV1.VALUE_draft,
    });
    emit('status-changed', updatedVersion);
    await updateGuideline(
        pendingUpdate.value.versionUuid,
        pendingUpdate.value.variantUuid,
        pendingUpdate.value.inputEvent
    );
    close();
};

const updateGuideline = async (
    versionUuid: string,
    policyGuidelineUuid: string,
    event: FocusEvent<HTMLSelectElement>
) => {
    try {
        await adminApi.updateRiskProfile(versionUuid, policyGuidelineUuid, { policyGuidelineUuid: event.target.value });
        if (errors.value.includes(policyGuidelineUuid)) {
            errors.value = errors.value.filter((error) => error !== policyGuidelineUuid);
        }
    } catch (error) {
        showToast(`Er ging iets mis. Probeer het opnieuw.`, 'risk-profile-update-guideline-toast', true);
        if (!errors.value.includes(policyGuidelineUuid)) errors.value.push(policyGuidelineUuid);
    }
};
</script>
