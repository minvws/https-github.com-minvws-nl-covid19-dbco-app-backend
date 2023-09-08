<template>
    <div class="message">
        <!-- eslint-disable-next-line vue/no-v-html : data provided by backend, no XSS risk -->
        <div class="message__body" v-html="bodyBeforeCustomText"></div>
        <!-- Only secure mails can have a custom text due to privacy concerns -->
        <div class="message__text-input" v-if="template.isSecure && templateHasCustomText" data-testid="custom-text">
            <button
                type="button"
                class="message__text-input__add-text"
                v-if="!textInputVisible"
                @click="textInputVisible = true"
                data-testid="custom-text-placeholder"
            >
                <i class="icon icon--add-circle" aria-hidden="true" aria-label="add icon"></i>
                <span class="text-primary">Voeg tekst toe</span>
            </button>
            <div class="message__text-input__form" v-else>
                <div class="message__text-input__form__close">
                    <BButton
                        variant="link"
                        size="sm"
                        class="d-flex align-items-center p-0"
                        @click="hideTextInput"
                        data-testid="close-textarea-button"
                    >
                        Verwijderen
                        <TrashIcon width="20px" class="ml-1" aria-hidden="true" aria-label="verwijder icon" />
                    </BButton>
                </div>
                <textarea
                    placeholder="Voeg tekst toe"
                    v-model="customText"
                    @change="change"
                    aria-label="custom text"
                ></textarea>
            </div>
        </div>

        <!-- eslint-disable vue/no-v-html : data provided by backend, no XSS risk -->
        <div class="message__body" v-html="bodyAfterCustomText"></div>
        <div class="message__footer" v-html="template.footer"></div>
        <!-- eslint-enable vue/no-v-html -->
        <div class="message__attachments" v-if="attachments.length > 0">
            <hr class="w-100" />
            <BFormGroup label="Bijlage(n)" v-slot="{ ariaDescribedby }">
                <BFormCheckboxGroup
                    id="attachments"
                    v-model="selectedAttachments"
                    :options="attachments"
                    :aria-describedby="ariaDescribedby"
                    name="attachments"
                    @change="change"
                    data-testid="checkbox-attachments"
                />
            </BFormGroup>
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { MessageTemplateModel } from '../SendMessageModal.vue';
import type { RenderedMailTemplate } from '@dbco/portal-api/mail.dto';
import TrashIcon from '@icons/icon-trash.svg?vue';

export default defineComponent({
    name: 'MessageTemplate',
    components: { TrashIcon },
    props: {
        template: {
            type: Object as PropType<RenderedMailTemplate>,
            required: true,
        },
    },
    data() {
        return {
            customText: undefined as string | undefined,
            customTextPlaceholder: '%custom_text_placeholder%',
            textInputVisible: false,
            selectedAttachments: [] as string[],
        };
    },
    computed: {
        attachments() {
            if (this.template.attachments) {
                return this.template.attachments.map(({ uuid: value, filename: text }) => ({ value, text }));
            }
            return [];
        },
        bodyBeforeCustomText() {
            return this.template?.body.split(this.customTextPlaceholder)[0];
        },
        bodyAfterCustomText() {
            return this.template?.body.split(this.customTextPlaceholder)[1];
        },
        templateHasCustomText() {
            return this.template?.body.includes(this.customTextPlaceholder) ?? false;
        },
    },
    methods: {
        change() {
            const model: MessageTemplateModel = {
                customText: this.customText,
                selectedAttachments: this.selectedAttachments,
            };

            this.$emit('messageTemplateChange', model);
        },
        hideTextInput() {
            this.customText = undefined;
            this.textInputVisible = false;
        },
    },
});
</script>
<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.message {
    background: white;
    display: flex;
    flex-direction: column;
    padding: 2.25rem;
    border-radius: $border-radius-medium;

    &__text-input {
        margin: 0.5rem 0 1.5rem;

        &__add-text {
            width: 100%;
            cursor: pointer;
            border: 1px dashed $lighter-grey;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: row;
            border-radius: $border-radius-medium;
            background: none;
            > span {
                margin-top: 3px;
                font-weight: 500;
            }
        }

        &__form {
            border: 1px solid $lightest-grey;
            display: flex;
            flex-direction: column;
            border-radius: $border-radius-medium;

            &__close {
                border-bottom: 1px solid $lightest-grey;
                display: flex;
                justify-content: flex-end;
                padding: 0.75rem;

                &__icon {
                    cursor: pointer;
                    display: flex;
                    flex-direction: row;
                    justify-items: flex-end;

                    border: none;
                    background: none;

                    svg {
                        width: 1.25rem;
                        height: 1.25rem;
                    }
                }
            }

            textarea {
                padding: 1rem;
                border: none;
                border-bottom-left-radius: $border-radius-medium;
                border-bottom-right-radius: $border-radius-medium;
            }
        }
    }

    &__footer {
        ul::before {
            color: $light-grey;
            content: 'Bijlage(n)';
            margin-bottom: 10px;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            align-items: flex-start;

            li {
                color: $bco-purple;
                font-weight: 500;
                margin-bottom: 0.25rem;

                a {
                    display: flex;
                    flex-direction: row;
                }
            }
        }
    }

    &__attachments {
        ::v-deep {
            .custom-checkbox {
                .custom-control-label {
                    &:before,
                    &:after {
                        top: 0.1rem;
                    }
                }
            }
        }
    }
}
</style>
