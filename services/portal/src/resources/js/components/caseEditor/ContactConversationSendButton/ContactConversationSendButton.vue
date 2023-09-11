<template>
    <FormSendEmail
        :caseUuid="caseUuid"
        :taskUuid="taskUuid"
        buttonLabel="Verstuur e-mail bij geen gehoor"
        :disabled="disabled"
        :emailVariant="mailVariant"
    />
</template>

<script lang="ts">
import FormSendEmail from '@/components/form/FormSendEmail/FormSendEmail.vue';
import { StoreType } from '@/store/storeType';
import { defineComponent } from 'vue';
import { MessageTemplateTypeV1 } from '@dbco/enum';
import { mapGetters } from '@/utils/vuex';

export default defineComponent({
    name: 'ContactConversationSendButton',
    components: { FormSendEmail },
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            mailVariant: MessageTemplateTypeV1.VALUE_missedPhone,
        };
    },
    computed: {
        ...mapGetters(StoreType.INDEX, {
            caseUuid: 'uuid',
        }),
        ...mapGetters(StoreType.TASK, {
            taskUuid: 'uuid',
        }),
    },
});
</script>
