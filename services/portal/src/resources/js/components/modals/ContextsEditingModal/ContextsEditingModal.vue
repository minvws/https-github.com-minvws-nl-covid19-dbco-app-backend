<template>
    <BModal id="ContextsEditingModal" size="xl">
        <div class="container">
            <h3 class="form-heading pb-2">Contexten</h3>
            <p>
                Plekken waar de index is geweest tijden de bronperiode met 3 of meer aanwezigen waar geen 1,5 meter
                afstand gehouden kon worden of plekken waar de index tijdens zijn besmettelijke periode is geweest en
                contact had met anderen.
            </p>
            <ContextEditingTable :group="ContextGroup.All" />
        </div>

        <template slot="modal-footer">
            <BButton variant="primary" @click="$bvModal.hide('ContextsEditingModal')">Sluiten</BButton>
        </template>
    </BModal>
</template>
<script lang="ts">
import ContextEditingTable from '@/components/caseEditor/ContextEditingTable/ContextEditingTable.vue';
import { ContextGroup } from '@/components/form/ts/formTypes';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'ContextsEditingModal',
    components: {
        ContextEditingTable,
    },
    setup() {
        return {
            ContextGroup,
        };
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
