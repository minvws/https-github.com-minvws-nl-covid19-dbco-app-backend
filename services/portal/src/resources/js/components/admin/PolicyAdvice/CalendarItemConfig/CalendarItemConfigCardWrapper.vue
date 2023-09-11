<template>
    <div>
        <Heading size="sm" as="h2" class="tw-mb-4">{{ props.config.label }}</Heading>
        <RadioCollapse
            :initialIsOpen="props.config.isHidden"
            :title="props.config.label + ' tonen in kalender?'"
            @change="toggleCollapse"
            v-if="props.config.isHideable"
        >
            <CalendarItemConfigCard :config="props.config" :versionStatus="props.versionStatus" />
        </RadioCollapse>
        <Card v-else class="tw-py-6 tw-px-8">
            <CalendarItemConfigCard :config="props.config" :versionStatus="props.versionStatus" />
        </Card>
    </div>
</template>

<script lang="ts" setup>
import { Card, Heading, RadioCollapse } from '@dbco/ui-library';
import type { PropType } from 'vue';
import type { PolicyVersionStatusV1 } from '@dbco/enum';
import CalendarItemConfigCard from './CalendarItemConfigCard.vue';
import type { CalendarItemConfig } from '@dbco/portal-api/admin.dto';
import { adminApi } from '@dbco/portal-api';
import { useRoute } from '@/router/router';

const { versionUuid, policyGuidelineUuid } = useRoute().params;

const props = defineProps({
    config: { type: Object as PropType<CalendarItemConfig>, required: true },
    versionStatus: { type: String as PropType<PolicyVersionStatusV1>, required: true },
});

async function toggleCollapse(event: ChangeEvent<HTMLInputElement>) {
    await adminApi.updateCalendarItemConfig(props.config.uuid, policyGuidelineUuid, versionUuid, {
        isHidden: event.target.value === 'true',
    });
}
</script>
