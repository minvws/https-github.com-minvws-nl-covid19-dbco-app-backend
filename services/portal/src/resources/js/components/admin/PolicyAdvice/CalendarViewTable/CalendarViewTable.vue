<template>
    <Table>
        <Thead>
            <Tr>
                <Th>Soort kalender</Th>
                <Th>Zichtbare items</Th>
            </Tr>
        </Thead>
        <Tbody v-if="calendarViews.length">
            <Tr
                v-for="view in calendarViews"
                :key="view.uuid"
                class="tw-cursor-pointer hover:tw-bg-violet-100"
                @click="showViewDetail(view.uuid)"
            >
                <Td>{{ view.label }}</Td>
                <Td class="tw-flex tw-justify-between align-items-center">
                    <ul v-if="view.calendarItems.length">
                        <li v-for="item in view.calendarItems">{{ item.label }}</li>
                    </ul>
                    <span v-else>-</span>
                    <Icon class="tw-w-3.5 tw-h-3.5 tw-text-violet-700" name="chevron-right" />
                </Td>
            </Tr>
        </Tbody>
        <Tbody v-else>
            <Tr>
                <Td colspan="3" class="tw-p-4 tw-text-center tw-w-full">
                    <span>Geen kalender views gevonden</span>
                </Td>
            </Tr>
        </Tbody>
    </Table>
</template>

<script lang="ts" setup>
import { Icon, Table, Tbody, Td, Th, Thead, Tr } from '@dbco/ui-library';
import { adminApi } from '@dbco/portal-api';
import type { CalendarView } from '@dbco/portal-api/admin.dto';
import { onMounted, ref } from 'vue';
import { useRouter } from '@/router/router';

const props = defineProps({
    versionUuid: { type: String, required: true },
});

const calendarViews = ref<CalendarView[]>([]);

onMounted(() => {
    void loadViews();
});
async function loadViews() {
    calendarViews.value = await adminApi.getCalendarViews(props.versionUuid);
}
async function showViewDetail(viewUuid: string) {
    await useRouter().push(`/beheren/beleidsversies/${props.versionUuid}/kalender-views/${viewUuid}`);
}
</script>
