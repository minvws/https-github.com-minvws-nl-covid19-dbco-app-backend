<template>
    <BModal title-tag="div" size="lg" centered scrollable :id="modalId" @hide="localErrors = {}" @show="getTemplate">
        <div slot="modal-title" class="send-message-modal-title">
            <h1 class="title pr-5">{{ template ? template.subject : modalTitle }}</h1>
            <ModalOption
                data-testid="option-email"
                v-model="recipient.email"
                @change="updateOptions"
                @focus="setEditing"
                :error="errors.email"
                label="Aan:"
                placeholder="Vul e-mailadres in"
                tooltip="Als je hier het e-mailadres aanpast, wordt dat ook in het dossier doorgevoerd."
            >
                <template v-if="taskUuid">
                    {{ recipient.firstname }} {{ recipient.lastname }} ({{ recipient.email }})
                </template>
                <template v-else>{{ displayName }} ({{ recipient.email }})</template>
            </ModalOption>
            <ModalOption
                data-testid="option-phone"
                v-if="isSecure"
                v-model="recipient.phone"
                @change="updateOptions"
                @focus="setEditing"
                :error="errors.phone"
                label="Sms-code naar:"
                placeholder="Vul telefoonnummer in"
                tooltip="Als je hier het telefoonnummer aanpast, wordt dat ook in het dossier doorgevoerd."
            >
                {{ recipient.phone }}
            </ModalOption>
            <ModalOption
                data-testid="option-language"
                v-model="recipient.emailLanguage"
                @change="updateOptions"
                @focus="setEditing"
                label="Taal:"
                :note="
                    isSecure && isPseudoBsnAvailable
                        ? 'Als ontvanger DigiD heeft kan daar ook mee worden ingelogd'
                        : undefined
                "
                :options="languageOptions"
                placeholder="Nederlands (standaard)"
                type="select"
            />
        </div>

        <div v-if="!template" class="text-center" data-testid="spinner-container">
            <BSpinner centered />
        </div>
        <div v-else>
            <FormInfo
                v-if="
                    recipient.emailLanguage != null &&
                    recipient.emailLanguage != template.language &&
                    status !== modalStatus.UpdatingOption
                "
                class="mb-4"
                :text="
                    generateSafeHtml(
                        'Dit bericht is niet beschikbaar in het <strong>{selectedLanguage}</strong>. Je kan het wel in het Nederlands versturen.',
                        { selectedLanguage }
                    )
                "
                data-testid="template-locale-notice"
            />
            <MessageTemplate @messageTemplateChange="onMessageTemplateChange" :template="template" />
        </div>

        <div slot="modal-footer">
            <BButton
                @click="sendMessage"
                class="align-items-center"
                :disabled="status !== modalStatus.Idle"
                size="lg"
                variant="primary"
                data-testid="send-message-button"
            >
                <template v-if="status === modalStatus.UpdatingOption">Bezig met opslaan</template>
                <template v-else>Bericht versturen</template>
                <BSpinner
                    v-if="[modalStatus.UpdatingOption, modalStatus.SendingMessage].includes(status)"
                    class="ml-2"
                    small
                    data-testid="send-message-spinner"
                />
            </BButton>
        </div>
    </BModal>
</template>

<script lang="ts">
import { messageApi } from '@dbco/portal-api';
import type { MessageTemplateTypeV1 } from '@dbco/enum';
import { EmailLanguageV1, emailLanguageV1Options, YesNoUnknownV1 } from '@dbco/enum';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { StoreType } from '@/store/storeType';
import { TaskActions } from '@/store/task/taskActions';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import MessageTemplate from './MessageTemplate/MessageTemplate.vue';
import ModalOption from './ModalOption/ModalOption.vue';
import { generateSafeHtml } from '@/utils/safeHtml';
import { mapActions, mapGetters } from '@/utils/vuex';
import { removeNullValues } from '@/utils/object';
import { SharedActions } from '@/store/actions';
import type { RenderedMailTemplate } from '@dbco/portal-api/mail.dto';

enum ModalStatus {
    Idle,
    EditingOption,
    UpdatingOption,
    SendingMessage,
}

interface SelectOption {
    value: unknown;
    text: string;
}

interface Recipient {
    firstname?: string;
    lastname?: string;
    email?: string;
    phone?: string;
    emailLanguage?: EmailLanguageV1;
}

export interface MessageTemplateModel {
    customText: string | undefined;
    selectedAttachments: string[];
}

interface Data {
    customText: string | undefined;
    selectedAttachments: string[];
    languageOptions: SelectOption[];
    localErrors: Record<string, string>;
    modalStatus: typeof ModalStatus;
    status: ModalStatus;
    template: RenderedMailTemplate | null;
}

export default defineComponent({
    name: 'SendMessageModal',
    components: { FormInfo, MessageTemplate, ModalOption },
    props: {
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
            customText: undefined,
            selectedAttachments: [],
            localErrors: {},
            languageOptions: Object.entries(emailLanguageV1Options).map(([key, value]) => ({
                value: key,
                text: value,
            })),
            modalStatus: ModalStatus,
            status: ModalStatus.Idle,
            template: null,
        } as Data;
    },
    computed: {
        ...mapGetters(StoreType.INDEX, {
            caseErrors: 'errors',
            caseFragments: 'fragments',
            displayName: 'indexDisplayName',
        }),
        ...mapGetters(StoreType.TASK, {
            taskErrors: 'errors',
            taskFragments: 'fragments',
        }),
        errors() {
            const { email, phone } = (this.taskUuid ? this.taskErrors.general : this.caseErrors.contact) || {};

            const errors = { ...this.localErrors };
            if (email) errors.email = JSON.parse(email).warning[0];

            // Secure mails require a phone number
            if (this.isSecure && phone) errors.phone = JSON.parse(phone).warning[0];

            return errors;
        },
        isPseudoBsnAvailable() {
            return this.taskUuid
                ? !!this.taskFragments.personalDetails?.bsnCensored
                : !!this.caseFragments.index?.bsnCensored;
        },
        isSecure() {
            return this.template?.isSecure ?? false;
        },
        selectedLanguage() {
            return emailLanguageV1Options[this.recipient.emailLanguage ?? EmailLanguageV1.VALUE_nl];
        },
        recipient(): Recipient {
            if (this.taskUuid) {
                // Only take the language if useAlternativeLanguage has been set to yes
                const emailLanguage =
                    this.taskFragments.alternativeLanguage?.useAlternativeLanguage === YesNoUnknownV1.VALUE_yes
                        ? this.taskFragments.alternativeLanguage.emailLanguage
                        : null;

                const { firstname, lastname, email, phone } = this.taskFragments.general || {};

                return removeNullValues({
                    firstname,
                    lastname,
                    email,
                    phone,
                    emailLanguage,
                });
            }

            // Only take the language if useAlternativeLanguage has been set to yes
            const emailLanguage =
                this.caseFragments.alternativeLanguage?.useAlternativeLanguage === YesNoUnknownV1.VALUE_yes
                    ? this.caseFragments.alternativeLanguage.emailLanguage
                    : null;

            return removeNullValues({
                firstname: this.caseFragments.index?.firstname,
                lastname: this.caseFragments.index?.lastname,
                email: this.caseFragments.contact?.email,
                phone: this.caseFragments.contact?.phone,
                emailLanguage,
            });
        },
    },
    methods: {
        ...mapActions(StoreType.INDEX, {
            updateIndexFragments: SharedActions.UPDATE_FORM_VALUE,
        }),
        ...mapActions(StoreType.TASK, {
            updateTaskFragments: TaskActions.UPDATE_TASK_FRAGMENT,
        }),
        generateSafeHtml,
        async getTemplate() {
            this.template = await (this.taskUuid
                ? messageApi.getEmailTemplateForContactUuid(this.caseUuid, this.mailVariant, this.taskUuid)
                : messageApi.getEmailTemplateForCaseUuid(this.caseUuid, this.mailVariant));
        },
        onMessageTemplateChange(model: MessageTemplateModel) {
            this.customText = model.customText;
            this.selectedAttachments = model.selectedAttachments;
        },
        async updateOptions() {
            this.localErrors = {};
            this.status = ModalStatus.UpdatingOption;

            let isDifferentLanguage = false;
            try {
                if (this.taskUuid) {
                    // Set to yes if a language has been selected
                    const useAlternativeLanguage = this.recipient.emailLanguage
                        ? YesNoUnknownV1.VALUE_yes
                        : this.taskFragments.alternativeLanguage?.useAlternativeLanguage;

                    isDifferentLanguage =
                        this.recipient.emailLanguage !== this.taskFragments.alternativeLanguage?.emailLanguage;

                    await this.updateTaskFragments({
                        alternativeLanguage: {
                            ...this.taskFragments.alternativeLanguage,
                            useAlternativeLanguage,
                            emailLanguage: this.recipient.emailLanguage,
                        },
                        general: {
                            ...this.taskFragments.general,
                            email: this.recipient.email,
                            phone: this.recipient.phone,
                        },
                        // eslint-disable-next-line no-warning-comments
                    } as any); // TODO: Remove any cast, type does not match what is expected!
                } else {
                    // Set to yes if a language has been selected
                    const useAlternativeLanguage = this.recipient.emailLanguage
                        ? YesNoUnknownV1.VALUE_yes
                        : this.caseFragments.alternativeLanguage?.useAlternativeLanguage;

                    isDifferentLanguage =
                        this.recipient.emailLanguage !== this.caseFragments.alternativeLanguage?.emailLanguage;

                    await this.updateIndexFragments({
                        alternativeLanguage: {
                            ...this.caseFragments.alternativeLanguage,
                            useAlternativeLanguage,
                            emailLanguage: this.recipient.emailLanguage,
                        },
                        contact: {
                            ...this.caseFragments.contact,
                            email: this.recipient.email,
                            phone: this.recipient.phone,
                        },
                    });
                }

                // Get template again after updating language
                if (isDifferentLanguage) {
                    await this.getTemplate();
                }
            } finally {
                this.status = ModalStatus.Idle;
            }
        },
        async sendMessage() {
            // Validate if there are any errors
            if (!this.validate()) return;

            this.status = ModalStatus.SendingMessage;

            // Don't send customText if empty
            const customText = !this.customText?.trim() ? undefined : this.customText;

            try {
                await (this.taskUuid
                    ? messageApi.sendMessageToContact(
                          this.caseUuid,
                          this.mailVariant,
                          this.taskUuid,
                          this.selectedAttachments,
                          customText
                      )
                    : messageApi.sendMessageToCase(
                          this.caseUuid,
                          this.mailVariant,
                          this.selectedAttachments,
                          customText
                      ));

                this.customText = undefined;
                this.$bvModal.hide(this.modalId);
                await this.$store.dispatch('index/LOAD_MESSAGES');
            } finally {
                this.status = ModalStatus.Idle;
            }
        },
        setEditing() {
            this.status = ModalStatus.EditingOption;
        },
        validate() {
            this.localErrors = {};

            if (!this.recipient.email || this.recipient.email.trim().length === 0) {
                this.localErrors.email = 'Vul een e-mailadres in';
            }

            // Secure mails require a phone number
            if (this.isSecure && (!this.recipient.phone || this.recipient.phone.trim().length === 0)) {
                this.localErrors.phone = 'Vul een telefoonnummer in';
            }

            return Object.values(this.errors).length === 0;
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

            .send-message-modal-title {
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
    }

    .modal-body {
        background: $body-bg !important;
        padding: 2rem;
    }

    .modal-footer {
        border-top: 1px solid $lightest-grey;
        display: flex;
        justify-content: flex-end;
        padding: 1rem 2rem;

        > * {
            margin: 0;
        }

        .btn {
            margin: 0;
        }
    }
}
</style>
