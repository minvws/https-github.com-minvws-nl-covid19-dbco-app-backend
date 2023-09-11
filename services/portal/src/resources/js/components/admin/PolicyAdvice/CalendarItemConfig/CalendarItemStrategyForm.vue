<template>
    <FormulateForm v-if="formValues" class="form-container tw-flex tw-w-full tw-flex-col tw-items-end">
        <HStack spacing="6" class="tw-w-full">
            <FormulateInput
                class="tw-grow tw-basis-0 w100"
                element-class="formulate-input-element formulate-input-element--select tw-bg-white"
                type="select"
                name="strategyType"
                label="Type"
                :disabled="disabled"
                :options="strategyTypeOptions"
                v-model="formValues.strategyType"
                @change="handleStrategyChange"
            />
            <FormulateInput
                class="tw-grow tw-basis-0 w100"
                element-class="formulate-input-element formulate-input-element--select tw-bg-white"
                type="select"
                name="relativeDateMutation"
                :label="formattedMutationLabel(sortedOperations[0].uuid)"
                :disabled="disabled"
                :options="dateOperationRelativeDayV1Options"
                v-model="sortedOperations[0].relativeDay"
                @change="handleOperationChange(sortedOperations[0].uuid)"
            />
            <FormulateInput
                class="tw-grow tw-basis-0 w100"
                element-class="formulate-input-element formulate-input-element--select tw-bg-white"
                type="select"
                name="originDateType"
                label="Uitgangsdatum"
                :disabled="disabled"
                :options="sourceDateOptions(sortedOperations[0].originDateType)"
                v-model="sortedOperations[0].originDateType"
                @change="handleOperationChange(sortedOperations[0].uuid)"
            />
        </HStack>
        <HStack
            v-if="sortedOperations.length > 1 && index !== 0"
            v-for="(operation, index) in sortedOperations"
            class="tw-w-2/3 tw-pl-2"
            spacing="6"
            :key="operation.uuid"
        >
            <FormulateInput
                class="tw-grow tw-basis-0 w100"
                element-class="formulate-input-element formulate-input-element--select tw-bg-white"
                type="select"
                name="relativeDateMutation"
                :label="formattedMutationLabel(operation.uuid)"
                :disabled="disabled"
                :options="dateOperationRelativeDayV1Options"
                v-model="operation.relativeDay"
                @change="handleOperationChange(operation.uuid)"
            />
            <FormulateInput
                class="tw-grow tw-basis-0 w100"
                element-class="formulate-input-element formulate-input-element--select tw-bg-white"
                type="select"
                name="originDateType"
                label="Uitgangsdatum"
                :disabled="disabled"
                :options="sourceDateOptions(operation.originDateType)"
                v-model="operation.originDateType"
                @change="handleOperationChange(operation.uuid)"
            />
        </HStack>
        <SaveModal
            cancelIsPrimary
            :isOpen="isOpen"
            title="Status terugzetten naar concept om je wijzigingen op te slaan?"
            @close="close"
            okLabel="Terugzetten"
            @ok="changePolicyStatus"
        >
            <p class="tw-mb-0">
                Je moet dit beleid eerst terugzetten naar concept om wijzigingen op te slaan. Let op dat het beleid dan
                niet meer actief wordt op de ingangsdatum, tenzij je het opnieuw klaarzet.
            </p>
        </SaveModal>
    </FormulateForm>
</template>

<script lang="ts" setup>
import { adminApi } from '@dbco/portal-api';
import { HStack, SaveModal, useOpenState } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { ContactOriginDateV1, IndexOriginDateV1 } from '@dbco/enum';
import {
    CalendarItemConfigStrategyIdentifierTypeV1,
    contactOriginDateV1Options,
    DateOperationIdentifierV1,
    dateOperationRelativeDayV1Options,
    indexOriginDateV1Options,
    PeriodCalendarStrategyTypeV1,
    periodCalendarStrategyTypeV1Options,
    pointCalendarStrategyTypeV1Options,
    PolicyVersionStatusV1,
} from '@dbco/enum';
import type { CalendarItemConfigStrategy } from '@dbco/portal-api/admin.dto';
import { useRoute } from '@/router/router';
import { useI18n } from 'vue-i18n-composable';
import { useEventBus } from '@/composables/useEventBus';
import { sortBy } from 'lodash';

const { versionUuid, policyGuidelineUuid } = useRoute().params;

const props = defineProps({
    configUuid: { type: String as PropType<string>, required: true },
    strategy: { type: Object as PropType<CalendarItemConfigStrategy>, required: true },
    versionStatus: { type: String as PropType<PolicyVersionStatusV1>, required: true },
});

const formValues = ref<CalendarItemConfigStrategy>();

const dateOperationsOrder = [
    DateOperationIdentifierV1.VALUE_default,
    DateOperationIdentifierV1.VALUE_min,
    DateOperationIdentifierV1.VALUE_max,
];
const sortedOperations = computed(() => {
    if (!formValues.value?.dateOperations.length) return [];
    return sortBy([...formValues.value.dateOperations], ({ identifierType }) => {
        const orderIndex = dateOperationsOrder.indexOf(identifierType);
        return orderIndex === -1 ? formValues.value?.dateOperations.length : orderIndex;
    });
});

function initFormValues() {
    formValues.value = { ...props.strategy };
}

onMounted(() => {
    initFormValues();
});

// Status
const { isOpen, close, open } = useOpenState({ onClose: () => initFormValues() });
const disabled = computed(
    () =>
        props.versionStatus === PolicyVersionStatusV1.VALUE_active ||
        props.versionStatus === PolicyVersionStatusV1.VALUE_old
);

async function changePolicyStatus() {
    await adminApi.updatePolicyVersion(versionUuid, {
        status: PolicyVersionStatusV1.VALUE_draft,
    });
    useEventBus().$emit('policy-status-change');
    close();
}

// Strategy
const strategyTypeOptions = computed(() =>
    props.strategy.strategyType in periodCalendarStrategyTypeV1Options
        ? {
              ...periodCalendarStrategyTypeV1Options,
              ...{
                  [PeriodCalendarStrategyTypeV1.VALUE_periodFadedStrategy]:
                      formValues.value?.identifierType === CalendarItemConfigStrategyIdentifierTypeV1.VALUE_periodStart
                          ? 'Begindatum onbekend'
                          : 'Einddatum onbekend',
              },
          }
        : pointCalendarStrategyTypeV1Options
);

function handleStrategyChange() {
    if (props.versionStatus !== PolicyVersionStatusV1.VALUE_active_soon) {
        return updateConfigStrategy();
    }
    open();
}

async function updateConfigStrategy() {
    if (!formValues.value?.strategyType) return;
    const data = await adminApi.updateCalendarItemConfigStrategy(
        props.configUuid,
        policyGuidelineUuid,
        props.strategy.uuid,
        versionUuid,
        { strategyType: formValues.value.strategyType }
    );
    formValues.value = data.strategies.find((strategy) => strategy.uuid === props.strategy.uuid);
}

// Date operations
function sourceDateOptions(originDateType: ContactOriginDateV1 | IndexOriginDateV1) {
    return originDateType in contactOriginDateV1Options ? contactOriginDateV1Options : indexOriginDateV1Options;
}

const { t } = useI18n();

function formattedMutationLabel(operationUuid: string) {
    const identifierType = formValues.value?.identifierType;
    const operationIndex = sortedOperations.value.findIndex((operation) => operation.uuid === operationUuid);
    const strategyType = formValues.value?.strategyType;

    return t(`components.calendarItemStrategyForm.mutationLabel.${identifierType}.${strategyType}.${operationIndex}`);
}

function handleOperationChange(operationUuid: string) {
    if (props.versionStatus !== PolicyVersionStatusV1.VALUE_active_soon) {
        return updateConfigOperation(operationUuid);
    }
    open();
}

async function updateConfigOperation(operationUuid: string) {
    const payload = formValues.value?.dateOperations.find((operation) => operation.uuid === operationUuid);
    if (!payload) return;
    if (typeof payload.relativeDay !== 'number') {
        payload.relativeDay = parseInt(payload.relativeDay);
    }
    const data = await adminApi.updateCalendarItemConfigOperation(
        props.configUuid,
        policyGuidelineUuid,
        operationUuid,
        props.strategy.uuid,
        versionUuid,
        payload
    );
    formValues.value = data.strategies.find((strategy) => strategy.uuid === props.strategy.uuid);
}
</script>
