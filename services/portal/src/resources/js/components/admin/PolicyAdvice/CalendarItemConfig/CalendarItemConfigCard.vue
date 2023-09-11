<template>
    <div>
        <div
            :class="props.config.isHideable && ['tw-p-6', 'tw-bg-gray-50', 'tw-shadow-inner']"
            v-if="props.config.itemType === CalendarItemV1.VALUE_point"
        >
            <CalendarItemStrategyForm
                :configUuid="props.config.uuid"
                :versionStatus="props.versionStatus"
                :strategy="config.strategies[0]"
            />
        </div>
        <div :class="props.config.isHideable && ['tw-p-6', 'tw-bg-gray-50', 'tw-shadow-inner']" v-else>
            <DetailHeading>Begin</DetailHeading>
            <CalendarItemStrategyForm
                :configUuid="props.config.uuid"
                :versionStatus="props.versionStatus"
                :strategy="setPeriodStrategy(CalendarItemConfigStrategyIdentifierTypeV1.VALUE_periodStart)"
            />

            <DetailHeading>Eind</DetailHeading>
            <CalendarItemStrategyForm
                :configUuid="props.config.uuid"
                :versionStatus="props.versionStatus"
                :strategy="setPeriodStrategy(CalendarItemConfigStrategyIdentifierTypeV1.VALUE_periodEnd)"
            />
        </div>
    </div>
</template>

<script lang="ts" setup>
import type { PropType } from 'vue';
import type { PolicyVersionStatusV1 } from '@dbco/enum';
import { CalendarItemV1, CalendarItemConfigStrategyIdentifierTypeV1 } from '@dbco/enum';
import CalendarItemStrategyForm from './CalendarItemStrategyForm.vue';
import DetailHeading from '../PolicyGuidelineDetail/DetailHeading.vue';
import type { CalendarItemConfig } from '@dbco/portal-api/admin.dto';

const props = defineProps({
    config: { type: Object as PropType<CalendarItemConfig>, required: true },
    versionStatus: { type: String as PropType<PolicyVersionStatusV1>, required: true },
});

function setPeriodStrategy(identifierType: CalendarItemConfigStrategyIdentifierTypeV1) {
    const strategyIndex = props.config.strategies.findIndex((strategy) => strategy.identifierType === identifierType);
    return props.config.strategies[strategyIndex];
}
</script>
