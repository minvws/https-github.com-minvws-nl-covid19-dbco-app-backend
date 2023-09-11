<template>
    <BModal id="ContactsEditingModal" size="xl">
        <div class="container">
            <h3 class="form-heading pb-2">Positief geteste bronpersonen</h3>
            <ContactEditingTable group="positivesource" class="mb-5" />
            <h3 class="form-heading pb-2">Bronpersonen met coronagerelateerde klachten</h3>
            <ContactEditingTable group="symptomaticsource" class="mb-5" />
            <h3 class="form-heading pb-2">Contacten binnen besmettelijke periode</h3>
            <ContactEditingTable group="contact" />
        </div>

        <template slot="modal-footer">
            <BButton variant="primary" @click="$bvModal.hide('ContactsEditingModal')">Sluiten</BButton>
        </template>
    </BModal>
</template>
<script lang="ts">
import ContactEditingTable from '@/components/caseEditor/ContactEditingTable/ContactEditingTable.vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'ContactsEditingModal',
    components: {
        ContactEditingTable,
    },
});
</script>

<style lang="scss" scoped>
::v-deep {
    .modal-footer {
        padding: 0.75rem;
        background: #f6f6fa;
        border-top: 1px solid #e5e3ef;

        .btn {
            flex: none;
        }
    }
}
</style>
