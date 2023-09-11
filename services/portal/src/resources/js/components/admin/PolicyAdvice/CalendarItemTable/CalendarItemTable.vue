<template>
    <Table class="tw-table-fixed">
        <Thead>
            <Tr>
                <Th>Standaard legendatekst</Th>
                <Th>Kleur</Th>
                <Th>Weergeven als</Th>
                <Th colspan="2">Index / contact</Th>
            </Tr>
        </Thead>
        <Tbody v-if="sortedCalendarItems.length">
            <Tr v-for="(item, index) in sortedCalendarItems" :key="item.uuid">
                <Td
                    class="form-container tw-pr-4 tw-align-top"
                    :class="index === sortedCalendarItems.length - 1 ? 'tw-border-none' : ''"
                    ><FormulateInput
                        type="text"
                        class="w100"
                        v-model="item.label"
                        :disabled="disabled"
                        :error="updateErrors.label && updateErrors.itemId === item.uuid ? updateErrors.label : null"
                        @change="triggerStatusCheck('update', { item, payload: { label: item.label } })"
                /></Td>
                <Td
                    class="tw-w-80 tw-pr-4 tw-align-top"
                    :class="index === sortedCalendarItems.length - 1 ? 'tw-border-none' : ''"
                >
                    <BDropdown
                        no-caret
                        class="tw-w-full tw-h-[42.13px] tw-flex tw-justify-start"
                        menu-class="w-100"
                        toggle-class="!tw-bg-white !tw-border-[#cecece]"
                        :disabled="disabled"
                    >
                        <template #button-content>
                            <div
                                class="tw-w-full tw-flex tw-justify-between tw-text-gray-950 tw-font-normal"
                                v-if="item.color"
                            >
                                <div class="tw-flex tw-gap-2">
                                    <Icon name="circle" :class="'tw-w-5 ' + colorOptionClass(item.color)" />
                                    {{ colorName(item) }}
                                </div>
                                <Icon name="chevron-down" class="tw-w-3" />
                            </div>
                            <div v-else class="tw-w-full tw-flex tw-justify-between tw-text-gray-950 tw-font-normal">
                                Selecteer een optie
                                <Icon name="chevron-down" class="tw-w-3" />
                            </div>
                        </template>
                        <BDropdownItem
                            v-for="(option, key) in colorOptionsForType(item)"
                            link-class="!tw-flex tw-gap-2 tw-items-start"
                            :disabled="!!colorOptionInUse(key).length"
                            :key="key"
                            @click="triggerStatusCheck('update', { item, payload: { color: key } })"
                            ><Icon name="circle" :class="'tw-w-5' + ' ' + colorOptionClass(key)" />
                            <span class="tw-block tw-whitespace-normal">
                                {{ option }}<span class="tw-block tw-text-gray-600">{{ colorOptionInUse(key) }}</span>
                            </span>
                        </BDropdownItem>
                    </BDropdown>
                </Td>
                <Td
                    class="tw-align-top tw-pt-6"
                    :class="index === sortedCalendarItems.length - 1 ? 'tw-border-none' : ''"
                    >{{ calendarItemV1Options[item.itemType] }}</Td
                >
                <Td
                    class="tw-align-top tw-pt-6"
                    :class="index === sortedCalendarItems.length - 1 ? 'tw-border-none' : ''"
                    >{{ policyPersonTypeV1Options[item.personType] }}</Td
                >
                <Td
                    class="tw-text-right tw-align-top"
                    :class="index === sortedCalendarItems.length - 1 ? 'tw-border-none' : ''"
                    ><Button
                        v-if="item.isDeletable"
                        size="sm"
                        variant="plain"
                        :disabled="disabled"
                        @click="triggerStatusCheck('delete', { item, payload: {} })"
                        >Verwijder</Button
                    >
                </Td>
            </Tr>
            <Tr
                ><Td colspan="4"
                    ><Button
                        iconLeft="plus"
                        variant="outline"
                        :disabled="disabled"
                        @click="triggerStatusCheck('create')"
                        >Nieuw item</Button
                    >
                </Td></Tr
            >
        </Tbody>
        <Tbody v-else>
            <Tr>
                <Td colspan="3" class="tw-p-4 tw-text-center tw-w-full">
                    <span>Geen kalender items gevonden</span>
                </Td>
            </Tr>
        </Tbody>
        <SaveModal
            okLabel="Doorgaan"
            title="Kalender item toevoegen"
            :isOpen="createModalIsOpen"
            :loading="creationIsPending"
            @close="closeCreateModal"
            @ok="createItem"
        >
            <FormulateForm class="form-container" :errors="createErrors">
                <FormulateInput
                    class="w100"
                    label="Standaard legendatekst"
                    name="label"
                    placeholder="Geef dit item een naam"
                    v-model="newCalendarItem.label"
                    @change="clearCreateErrors"
                />
                <FormulateInput
                    label="Voor wie is dit item?"
                    type="radio"
                    name="personType"
                    v-model="newCalendarItem.personType"
                    :options="policyPersonTypeV1Options"
                    @input="clearCreateErrors"
                />
                <FormulateInput
                    label="Weergeven als"
                    type="radio"
                    name="itemType"
                    :options="calendarItemV1Options"
                    @input="changeNewItemType"
                />
                <div>
                    <label for="new-calendar-item-color">Kleur</label>
                    <BDropdown
                        no-caret
                        id="new-calendar-item-color"
                        class="tw-w-full tw-h-[42.13px] tw-flex tw-justify-start tw-mb-4"
                        menu-class="w-100"
                        toggle-class="!tw-bg-white !tw-border-[#cecece]"
                    >
                        <template #button-content>
                            <div
                                class="tw-w-full tw-flex tw-justify-between tw-text-gray-950 tw-font-normal"
                                v-if="newCalendarItem?.color"
                            >
                                <div class="tw-flex tw-gap-2">
                                    <Icon name="circle" :class="'tw-w-5 ' + colorOptionClass(newCalendarItem.color)" />
                                    {{ colorName(newCalendarItem) }}
                                </div>
                                <Icon name="chevron-down" class="tw-w-3" />
                            </div>
                            <div v-else class="tw-w-full tw-flex tw-justify-between tw-text-gray-950 tw-font-normal">
                                Selecteer een optie
                                <Icon name="chevron-down" class="tw-w-3" />
                            </div>
                        </template>
                        <BDropdownItem
                            v-for="(option, key) in colorOptionsForType(newCalendarItem)"
                            link-class="!tw-flex tw-gap-2 tw-items-start"
                            :disabled="!!colorOptionInUse(key).length"
                            :key="key"
                            @click="setNewItemColor(key)"
                            ><Icon name="circle" :class="'tw-w-5' + ' ' + colorOptionClass(key)" />
                            <span class="tw-block tw-whitespace-normal">
                                {{ option }}<span class="tw-block tw-text-gray-600">{{ colorOptionInUse(key) }}</span>
                            </span>
                        </BDropdownItem>
                    </BDropdown>
                    <!-- Match VueFormulate error markup and styling -->
                    <ul v-if="createErrors.color" class="formulate-input-errors tw-ml-0 tw-pl-0">
                        <li
                            role="status"
                            aria-live="polite"
                            class="formulate-input-error error-type-warning tw-flex tw-items-center tw-mb-2"
                        >
                            <i class="icon tw-m-1 tw-mr-2 icon--error-warning"></i>Selecteer een kleur
                        </li>
                    </ul>
                </div>
            </FormulateForm>
        </SaveModal>
        <SaveModal
            cancelIsPrimary
            :isOpen="statusChangeModalIsOpen"
            title="Status terugzetten naar concept om je wijzigingen op te slaan?"
            @close="closeStatusChangeModal"
            okLabel="Terugzetten"
            @ok="handleStatusChange"
        >
            <p class="tw-mb-0">
                Je moet dit beleid eerst terugzetten naar concept om wijzigingen op te slaan. Let op dat het beleid dan
                niet meer actief wordt op de ingangsdatum, tenzij je het opnieuw klaarzet.
            </p>
        </SaveModal>
        <SaveModal
            cancelIsPrimary
            okLabel="Verwijderen"
            title="Weet je zeker dat je dit kalender item wil verwijderen?"
            :isOpen="deleteModalIsOpen"
            @close="closeDeleteModal"
            @ok="deleteItem"
        />
    </Table>
</template>

<script lang="ts" setup>
import { sortBy } from 'lodash';
import { Button, Icon, SaveModal, Table, Tbody, Td, Th, Thead, Tr, useOpenState } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { computed, onMounted, ref } from 'vue';
import { adminApi } from '@dbco/portal-api';
import { colorName, colorOptionsForType } from '@/utils/calendar';
import type { CalendarItem } from '@dbco/portal-api/admin.dto';
import type { CalendarItemV1 } from '@dbco/enum';
import {
    policyPersonTypeV1Options,
    calendarItemV1Options,
    CalendarPeriodColorV1,
    CalendarPointColorV1,
    PolicyVersionStatusV1,
    PolicyPersonTypeV1,
} from '@dbco/enum';
import type { AxiosError } from 'axios';

const props = defineProps({
    versionStatus: { type: String as PropType<PolicyVersionStatusV1>, required: true },
    versionUuid: { type: String, required: true },
    disabled: { type: Boolean, default: false, required: false },
});

// Load and render CalendarItems
const calendarItems = ref<CalendarItem[]>([]);
const calenderItemsOrder = [PolicyPersonTypeV1.VALUE_index, PolicyPersonTypeV1.VALUE_contact];
const sortedCalendarItems = computed(() => {
    return sortBy([...calendarItems.value], ({ personType }) => {
        const orderIndex = calenderItemsOrder.indexOf(personType);
        return orderIndex === -1 ? calendarItems.value.length : orderIndex;
    });
});
onMounted(() => {
    void loadItems();
});
async function loadItems() {
    calendarItems.value = await adminApi.getCalendarItems(props.versionUuid);
}

function colorOptionClass(color: CalendarPeriodColorV1 | CalendarPointColorV1) {
    switch (color) {
        case CalendarPeriodColorV1.VALUE_light_red:
            return 'tw-text-red-100';
        case CalendarPeriodColorV1.VALUE_light_orange:
            return 'tw-text-orange-400 tw-opacity-20';
        case CalendarPeriodColorV1.VALUE_light_yellow:
            return 'tw-text-yellow-100';
        case CalendarPeriodColorV1.VALUE_light_green:
            return 'tw-text-green-100';
        case CalendarPeriodColorV1.VALUE_light_blue:
            return 'tw-text-blue-100';
        case CalendarPeriodColorV1.VALUE_light_purple:
            return 'tw-text-violet-100';
        case CalendarPeriodColorV1.VALUE_light_lavender:
            return 'tw-text-violet-400 tw-opacity-20';
        case CalendarPeriodColorV1.VALUE_light_pink:
            return 'tw-text-pink-400 tw-opacity-20';
        case CalendarPointColorV1.VALUE_red:
            return 'tw-text-red-600';
        case CalendarPointColorV1.VALUE_orange:
            return 'tw-text-orange-400';
        case CalendarPointColorV1.VALUE_yellow:
            return 'tw-text-yellow-400';
        case CalendarPointColorV1.VALUE_green:
            return 'tw-text-seaGreen-400';
        case CalendarPointColorV1.VALUE_azure_blue:
            return 'tw-text-blue-500';
        case CalendarPointColorV1.VALUE_purple:
            return 'tw-text-violet-700';
        case CalendarPointColorV1.VALUE_lavender:
            return 'tw-text-violet-400';
        case CalendarPointColorV1.VALUE_pink:
            return 'tw-text-pink-400';
    }
}

function colorOptionInUse(key: string) {
    const itemUsingColor = calendarItems.value.find((item) => item.color === key);
    if (!itemUsingColor) return '';
    return `Gebruikt voor ${itemUsingColor.label}`;
}

// Active soon status change modal
const emit = defineEmits(['status-changed']);
const { isOpen: statusChangeModalIsOpen, close: closeStatusChangeModal, open: openStatusChangeModal } = useOpenState();

type StatusChangeTrigger = 'create' | 'delete' | 'update' | undefined;
const statusChangeTrigger = ref<StatusChangeTrigger>();

type PendingUpdate =
    | {
          item: CalendarItem;
          payload?: Partial<CalendarItem>;
      }
    | undefined;

const pendingUpdate = ref<PendingUpdate>();

function doPendingAction(triggerType: StatusChangeTrigger, updatePayload?: PendingUpdate) {
    switch (triggerType) {
        case 'update':
            void updateItem(updatePayload);
            break;
        case 'create':
            openCreateModal();
            break;
        case 'delete':
            if (updatePayload?.item) {
                pendingUpdate.value = { item: updatePayload.item };
            }
            openDeleteModal();
    }
}

async function handleStatusChange() {
    const updatedVersion = await adminApi.updatePolicyVersion(props.versionUuid, {
        status: PolicyVersionStatusV1.VALUE_draft,
    });
    emit('status-changed', updatedVersion);
    doPendingAction(statusChangeTrigger.value, pendingUpdate.value);
    closeStatusChangeModal();
}

function triggerStatusCheck(triggerType: StatusChangeTrigger, updatePayload?: PendingUpdate) {
    if (props.versionStatus !== PolicyVersionStatusV1.VALUE_active_soon) {
        return doPendingAction(triggerType, updatePayload);
    }
    statusChangeTrigger.value = triggerType;
    pendingUpdate.value = updatePayload;
    openStatusChangeModal();
}

// Delete CalendarItem
const { close: closeDeleteModal, isOpen: deleteModalIsOpen, open: openDeleteModal } = useOpenState();

async function deleteItem() {
    if (!pendingUpdate.value?.item.uuid) return;
    await adminApi.deleteCalendarItem(props.versionUuid, pendingUpdate.value.item.uuid);
    void loadItems();
    closeDeleteModal();
}

// Create new CalendarItem
const newCalendarItem = ref<Partial<CalendarItem>>({ label: undefined, personType: undefined });
const creationIsPending = ref(false);
const createErrors = ref<AnyObject>({});
const {
    close: closeCreateModal,
    isOpen: createModalIsOpen,
    open: openCreateModal,
} = useOpenState({ onClose: () => (newCalendarItem.value = { label: undefined, personType: undefined }) });

function changeNewItemType(type: CalendarItemV1) {
    clearCreateErrors();
    newCalendarItem.value = { ...newCalendarItem.value, ...{ itemType: type } };
    if (newCalendarItem.value?.color) {
        const typeColors = colorOptionsForType(newCalendarItem.value);
        if (!Object.keys(typeColors).includes(newCalendarItem.value.color)) {
            delete newCalendarItem.value.color;
        }
    }
}

function clearCreateErrors() {
    createErrors.value = {};
}

function setNewItemColor(key: CalendarPeriodColorV1 | CalendarPointColorV1) {
    clearCreateErrors();
    newCalendarItem.value = { ...newCalendarItem.value, ...{ color: key } };
}

async function createItem() {
    creationIsPending.value = true;
    try {
        await adminApi.createCalendarItem(props.versionUuid, newCalendarItem.value);
        clearCreateErrors();
        void loadItems();
        closeCreateModal();
    } catch (error) {
        const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
        if (response?.data.errors) {
            const { label, itemType, personType, color } = response.data.errors;
            if (label) createErrors.value = { label: JSON.stringify({ warning: ['Geef het kalender item een naam'] }) };
            if (itemType)
                createErrors.value = {
                    ...createErrors.value,
                    ...{ itemType: JSON.stringify({ warning: ['Selecteer een periode of dag'] }) },
                };
            if (personType)
                createErrors.value = {
                    ...createErrors.value,
                    ...{ personType: JSON.stringify({ warning: ['Selecteer index of contact'] }) },
                };
            if (color)
                createErrors.value = {
                    ...createErrors.value,
                    ...{ color: JSON.stringify({ warning: ['Selecteer een kleur'] }) },
                };
        }
    } finally {
        creationIsPending.value = false;
    }
}

// Update CalendarItem
const updateErrors = ref<AnyObject>({});

function clearUpdateErrors() {
    updateErrors.value = {};
}

async function updateItem(updatePayload: PendingUpdate) {
    if (!updatePayload?.item.uuid || !updatePayload?.payload) return;
    try {
        await adminApi.updateCalendarItem(props.versionUuid, updatePayload.item.uuid, updatePayload.payload);
        clearUpdateErrors();
        void loadItems();
    } catch (error) {
        const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
        if (response?.data.errors) {
            const { label, color } = response.data.errors;
            if (label)
                updateErrors.value = {
                    label: JSON.stringify({ warning: ['Geef het kalender item een naam'] }),
                    itemId: updatePayload.item.uuid,
                };
            if (color)
                updateErrors.value = {
                    ...updateErrors.value,
                    ...{ color: JSON.stringify({ warning: ['Selecteer een kleur'] }), itemId: updatePayload.item.uuid },
                };
        }
    }
}
</script>
