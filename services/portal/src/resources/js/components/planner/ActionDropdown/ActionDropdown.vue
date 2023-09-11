<template>
    <BDropdown v-if="itemHasActions" variant="link" no-caret right toggle-class="text-right">
        <template #button-content>
            <span class="tw-sr-only">Acties</span>
            <i class="icon icon--options icon--center" />
        </template>
        <BDropdownItem v-if="item.isEditable" @click="$emit('edit')"> Details Wijzigen </BDropdownItem>
        <BDropdownItem v-if="item.canChangeOrganisation" @click="$emit('changeOrganisation')">
            GGD-regio Wijzigen
        </BDropdownItem>
        <BDropdownItem v-if="item.isClosable" @click="$emit('close')"> Sluiten </BDropdownItem>
        <BDropdownItem v-if="item.isReopenable" @click="$emit('reopen')"> Heropenen </BDropdownItem>
        <template v-if="item.isDeletable">
            <BDropdownDivider />
            <BDropdownItem @click="$emit('delete')"> Verwijderen </BDropdownItem>
        </template>
    </BDropdown>
</template>

<script lang="ts">
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'ActionDropdown',
    props: {
        item: {
            type: Object as PropType<PlannerCaseListItem>,
            required: true,
        },
    },
    computed: {
        itemHasActions() {
            const { isClosable, isDeletable, isEditable, isReopenable, canChangeOrganisation } = this.item;
            return isClosable || isDeletable || isEditable || isReopenable || canChangeOrganisation;
        },
    },
});
</script>
