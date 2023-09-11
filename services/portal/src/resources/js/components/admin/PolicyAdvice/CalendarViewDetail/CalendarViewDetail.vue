<template>
    <FullScreenModal :path="['Kalender views', view?.label ?? '']" @onClose="useRouter().back()">
        <template v-slot:header>
            <p class="tw-mb-0 tw-text-lg">
                <span>Beheren / </span>
                <strong>Beheermodule kalender index</strong>
            </p>
        </template>
        <template v-slot:action>
            <div class="policy-guideline-modal-action tw-flex tw-gap-5 tw-items-baseline">
                <LastUpdated />
                <Button size="sm" @click="useRouter().back()">Terug naar beleid</Button>
            </div>
        </template>
        <div class="tw-h-screen">
            <Card>
                <FormulateForm v-if="view?.uuid" class="form-container">
                    <FormulateInput
                        label="Zichtbare kalender items"
                        type="checkbox"
                        :disabled="disabled"
                        :options="itemOptions"
                        :value="viewCalendarItems"
                        @input="handleInput"
                    />
                </FormulateForm>
                <p v-else>Geen kalender view gevonden</p>
            </Card>
            <SaveModal
                cancelIsPrimary
                :isOpen="isOpen"
                title="Status terugzetten naar concept om je wijzigingen op te slaan?"
                @close="close"
                okLabel="Terugzetten"
                @ok="handleStatusChange"
            >
                <p class="tw-mb-0">
                    Je moet dit beleid eerst terugzetten naar concept om wijzigingen op te slaan. Let op dat het beleid
                    dan niet meer actief wordt op de ingangsdatum, tenzij je het opnieuw klaarzet.
                </p>
            </SaveModal>
        </div>
    </FullScreenModal>
</template>

<script lang="ts" setup>
import { Button, Card, SaveModal, useOpenState } from '@dbco/ui-library';
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import LastUpdated from '@/components/caseEditor/LastUpdated/LastUpdated.vue';
import { adminApi } from '@dbco/portal-api';
import { useRoute, useRouter } from '@/router/router';
import { ref, onMounted, computed } from 'vue';
import type { CalendarItem, CalendarView } from '@dbco/portal-api/admin.dto';
import { PolicyVersionStatusV1 } from '@dbco/enum';

const { versionUuid, viewUuid } = useRoute().params;

const calendarItems = ref<CalendarItem[]>([]);
const view = ref<CalendarView>();
const itemOptions = computed(() => {
    return calendarItems.value.map((item) => ({ value: item.uuid, label: item.label }));
});
const viewCalendarItems = computed(() => {
    return view.value?.calendarItems.map((item) => item.uuid);
});

const disabled = computed(
    () =>
        view.value?.policyVersionStatus === PolicyVersionStatusV1.VALUE_active ||
        view.value?.policyVersionStatus === PolicyVersionStatusV1.VALUE_old
);

const { isOpen, close, open } = useOpenState({
    onClose: () => loadView(),
});

onMounted(() => {
    void loadView();
});

async function loadView() {
    [calendarItems.value, view.value] = await Promise.all([
        adminApi.getCalendarItems(versionUuid),
        adminApi.getCalendarView(versionUuid, viewUuid),
    ]);
}

const pendingUpdate = ref<string[]>([]);
function handleInput(selectedItems: string[]) {
    if (view.value?.policyVersionStatus !== PolicyVersionStatusV1.VALUE_active_soon) {
        return updateView(selectedItems);
    }
    pendingUpdate.value = selectedItems;
    open();
}

async function handleStatusChange() {
    await adminApi.updatePolicyVersion(versionUuid, {
        status: PolicyVersionStatusV1.VALUE_draft,
    });
    await updateView(pendingUpdate.value);
    close();
}

async function updateView(selectedItems: string[]) {
    const itemsForUpdate = selectedItems.length
        ? calendarItems.value.filter((item) => selectedItems.some((calendarItemUuid) => calendarItemUuid === item.uuid))
        : [];
    const viewForUpdate = {
        ...view.value,
        ...{ calendarItems: itemsForUpdate },
    };
    view.value = await adminApi.updateCalendarView(viewForUpdate);
}
</script>
