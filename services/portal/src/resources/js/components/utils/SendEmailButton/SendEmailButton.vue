<template>
    <div>
        <BButton @click="showModal" data-testid="showModalButton" :disabled="isDisabled" :variant="buttonVariant">
            <slot />
        </BButton>
        <SendMessageModal
            :modalId="modalId"
            :modalTitle="taskUuid ? 'Contact mailen' : 'Index mailen'"
            :mailVariant="mailVariant"
            :caseUuid="caseUuid"
            :taskUuid="taskUuid"
        />
    </div>
</template>

<script lang="ts">
import SendMessageModal from '@/components/modals/SendMessageModal/SendMessageModal.vue';
import type { MessageTemplateTypeV1 } from '@dbco/enum';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

let uuid = 0;

export default defineComponent({
    name: 'SendEmailButton',
    components: { SendMessageModal },
    data() {
        return {
            modalId: '',
        };
    },
    created() {
        this.modalId = `sendEmailButton_${uuid++}`;
    },
    props: {
        buttonVariant: {
            type: String,
            required: false,
            default: 'outline-primary',
        },
        caseUuid: {
            type: String,
            required: true,
        },
        taskUuid: {
            type: String,
            required: false,
        },
        mailVariant: {
            type: String as PropType<MessageTemplateTypeV1>,
            required: true,
        },
        isDisabled: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        showModal() {
            this.$bvModal.show(this.modalId);
        },
    },
});
</script>
