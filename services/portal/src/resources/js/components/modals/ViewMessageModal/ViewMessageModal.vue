<template>
    <BModal
        visible
        title-tag="div"
        size="lg"
        centered
        hide-footer
        scrollable
        :id="modalId"
        @hide="$emit('hide')"
        @show="getMessage"
    >
        <div slot="modal-title" class="view-message-modal-title">
            <h1 class="title pr-5">
                {{ message ? message.subject : modalTitle }}
            </h1>
            <dl v-if="message" data-testid="message-info">
                <dt>Aan:</dt>
                <dd>{{ message.toEmail }}</dd>

                <template v-if="message.isSecure">
                    <dt data-testid="login-method-label">Inloggen:</dt>
                    <dd data-testid="login-method-value">{{ loginMethod }}</dd>
                </template>

                <dt>Verzonden op:</dt>
                <dd>{{ $filters.dateTimeFormatLong(message.createdAt) }}</dd>

                <template v-if="message.expiresAt">
                    <dt data-testid="expires-at-label">Beschikbaar tot:</dt>
                    <dd data-testid="expires-at-value">{{ $filters.dateTimeFormatLong(message.expiresAt) }}</dd>
                </template>

                <!-- <dt>Status:</dt>
                <dd>{{ messageStatusOptions[message.status] }}</dd> -->
            </dl>
        </div>

        <div v-if="!message" class="text-center" data-testid="spinner-container">
            <BSpinner centered />
        </div>
        <template v-else>
            <div class="message">
                <!-- eslint-disable-next-line vue/no-v-html : data provided by backend, user input in additional info text is escaped -->
                <div v-html="message.text"></div>
                <template v-if="message.attachments && message.attachments.length > 0">
                    <hr class="w-100" />
                    <dt class="text-muted mt-3 mb-1">Bijlage(n)</dt>
                    <dd
                        v-for="attachment in message.attachments"
                        :key="attachment.uuid"
                        class="mt-1 mb-2"
                        :data-testid="`attachment-${attachment.uuid}`"
                    >
                        {{ attachment.fileName }}
                    </dd>
                </template>
            </div>
        </template>
    </BModal>
</template>

<script lang="ts">
import { messageApi } from '@dbco/portal-api';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { defineComponent } from 'vue';
import MessageTemplate from '../SendMessageModal/MessageTemplate/MessageTemplate.vue';
import { messageStatusV1Options } from '@dbco/enum';
import type { Message } from '@dbco/portal-api/message.dto';

export default defineComponent({
    name: 'ViewMessageModal',
    components: { FormInfo, MessageTemplate },
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
        messageUuid: {
            type: String,
            required: true,
        },
        modalId: {
            type: String,
            required: true,
        },
        modalTitle: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            message: null as Message | null,
            messageStatusOptions: messageStatusV1Options,
        };
    },
    computed: {
        loginMethod() {
            const loginMethod = [];
            if (this.message?.telephone) loginMethod.push(`SMS-code via ${this.message.telephone}`);
            if (this.message?.isIdentified) loginMethod.push('inloggen met DigiD');

            let sentence = loginMethod.join(' of ');

            // Ensure capitalization of first letter
            return sentence.charAt(0).toUpperCase() + sentence.slice(1);
        },
    },
    methods: {
        async getMessage() {
            this.message = await messageApi.getMessage(this.caseUuid, this.messageUuid);
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

::v-deep .modal-content {
    .modal-header {
        border-bottom: 1px solid $lightest-grey;
        padding: 2rem 2rem 1rem 2rem;

        .modal-title {
            width: 100%;

            .view-message-modal-title {
                display: flex;
                flex-direction: column;
                width: calc(100% + 2rem);

                .title {
                    margin-bottom: 0.5rem;
                    font-size: 1.5rem;
                    line-height: 1.75rem;
                }
            }
        }

        dl {
            display: grid;
            grid-template-columns: 120px auto;
            grid-gap: 0.5rem;
            margin-top: 0.75rem;

            dt {
                grid-column: 1;
            }

            dd {
                grid-column: 2;
            }

            dt,
            dd {
                color: $light-grey;
                font-size: 0.75rem;
                font-weight: normal;
                line-height: 1rem;
                margin: 0;
            }
        }
    }

    .modal-body {
        background: $body-bg !important;
        padding: 2rem;

        .message {
            background: white;
            display: flex;
            flex-direction: column;
            padding: 2.25rem;
            border-radius: $border-radius-medium;
        }
    }
}
</style>
