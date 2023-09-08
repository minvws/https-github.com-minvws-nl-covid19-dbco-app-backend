<template>
    <div>
        <SendEmailButton
            :buttonVariant="buttonVariant"
            :caseUuid="caseUuid"
            :taskUuid="taskUuid"
            :mailVariant="emailVariant"
            :isDisabled="disabled"
            data-testid="send-email-button"
        >
            <div v-if="buttonVariant === 'link'" class="content">
                <span>{{ buttonLabel }}</span>
                <ChevronRightIcon width="24" height="24" />
            </div>
            <template v-else>{{ buttonLabel }}</template>
        </SendEmailButton>

        <div v-if="emailSent && userCanEdit" class="mt-2 email-sent">
            <i class="icon icon--m0 icon--checkmark"></i>
            Bericht verzonden aan {{ emailSent.toEmail }} op {{ $filters.dateTimeFormatLong(emailSent.createdAt) }}
        </div>
    </div>
</template>

<script lang="ts">
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import SendEmailButton from '@/components/utils/SendEmailButton/SendEmailButton.vue';
import { SharedActions } from '@/store/actions';
import { StoreType } from '@/store/storeType';
import type { MessageTemplateTypeV1 } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import { mapActions, mapGetters } from '@/utils/vuex';
import { compareDesc } from 'date-fns';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { MessageSummary } from '@dbco/portal-api/message.dto';
import ChevronRightIcon from '@icons/chevron-right.svg?vue';

export default defineComponent({
    name: 'FormSendEmail',
    components: { SendEmailButton, FormInfo, ChevronRightIcon },
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
        taskUuid: {
            type: String,
            required: false,
        },
        buttonLabel: {
            type: String,
            required: true,
        },
        buttonVariant: {
            type: String,
            required: false,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
        emailVariant: {
            type: String as PropType<MessageTemplateTypeV1>,
            required: true,
        },
    },
    inject: {
        rootModel: {
            default: {},
        },
    },
    data() {
        return {};
    },
    created() {
        if (this.userCanEdit) void this.getMessages();
    },
    computed: {
        ...mapGetters(StoreType.INDEX, ['messages']),
        emailSent() {
            return this.messages
                .filter(
                    (message) =>
                        // Match template type
                        message.mailVariant === this.emailVariant &&
                        // Match case
                        message.caseUuid === this.caseUuid &&
                        // Match task if given
                        (!this.taskUuid || message.taskUuid === this.taskUuid)
                )
                .sort((a: MessageSummary, b: MessageSummary) =>
                    compareDesc(new Date(a.createdAt), new Date(b.createdAt))
                )[0];
        },
        userCanEdit,
    },
    methods: {
        ...mapActions(StoreType.INDEX, { getMessages: SharedActions.LOAD_MESSAGES }),
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.content {
    color: $bco-purple;
    align-items: center;
    font-size: 14px;
    text-align: left;

    &.disabled {
        color: $grey;
        cursor: not-allowed;
    }
}

.email-sent {
    color: $dark-grey;

    .icon {
        background-color: $light-green;
    }
}

::v-deep {
    .btn {
        width: auto;
    }

    .btn-link {
        padding: 0;
    }
}
</style>
