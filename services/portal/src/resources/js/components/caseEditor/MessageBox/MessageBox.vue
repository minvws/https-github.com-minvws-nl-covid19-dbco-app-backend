<template>
    <div>
        <Messages :taskUuid="taskUuid" @select="(id) => (selectedMessageUuid = id)" />
        <ViewMessageModal
            v-if="selectedMessageUuid"
            @hide="selectedMessageUuid = null"
            :caseUuid="caseUuid"
            :messageUuid="selectedMessageUuid"
            :modalId="`view-message-modal-${selectedMessageUuid}`"
            modalTitle="Bekijk bericht"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ViewMessageModal from '@/components/modals/ViewMessageModal/ViewMessageModal.vue';
import Messages from '@/components/caseEditor/MessageBox/Messages.vue';

export default defineComponent({
    name: 'MessageBox',
    components: { ViewMessageModal, Messages },
    props: {
        taskUuid: {
            type: String,
            required: false,
            default: null,
        },
    },
    computed: {
        caseUuid() {
            return this.$store.getters['index/uuid'];
        },
    },
    data() {
        return {
            selectedMessageUuid: null,
        };
    },
});
</script>
