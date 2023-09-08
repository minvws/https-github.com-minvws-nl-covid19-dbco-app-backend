<template>
    <BForm @submit.prevent="submit">
        <fieldset>
            <BFormGroup :label="t('components.createCallToAction.role.label')">
                <BDropdown
                    :class="{ placeholder: !selectedRole }"
                    :text="selectedRole ? t(`roles.user_alt`) : t('components.createCallToAction.role.placeholder')"
                    toggle-class="d-flex align-items-center justify-content-between"
                    block
                    lazy
                    variant="outline-primary"
                    @show="inputInvalid.role = false"
                >
                    <BDropdownItem v-for="role in roles" :key="role" @click="selectedRole = role">{{
                        t(`roles.user_alt`)
                    }}</BDropdownItem>
                </BDropdown>
                <BFormInvalidFeedback :state="!inputInvalid.role">
                    <i class="icon icon--error-warning" />
                    {{ t('components.createCallToAction.role.required') }}
                </BFormInvalidFeedback>
            </BFormGroup>
            <BFormGroup :label="t('components.createCallToAction.date.label')">
                <BFormDatepicker
                    :class="{ placeholder: !selectedDate }"
                    :min="new Date()"
                    :placeholder="t('components.createCallToAction.date.placeholder')"
                    calendar-width="100%"
                    hide-header
                    label-help
                    locale="nl"
                    menu-class="w-100"
                    nav-button-variant="primary"
                    right
                    start-weekday="1"
                    v-model="selectedDate"
                    weekday-header-format="narrow"
                    @input="inputInvalid.date = false"
                />
                <BFormInvalidFeedback :state="!inputInvalid.date">
                    <i class="icon icon--error-warning" />
                    {{ t('components.createCallToAction.date.required') }}
                </BFormInvalidFeedback>
            </BFormGroup>
            <BFormGroup :label="t('components.createCallToAction.subject.label')">
                <BFormInput
                    maxlength="250"
                    v-model="subject"
                    :placeholder="t('components.createCallToAction.subject.placeholder')"
                    @input="inputInvalid.subject = false"
                />
                <BFormInvalidFeedback :state="!inputInvalid.subject">
                    <i class="icon icon--error-warning" />
                    {{ t('components.createCallToAction.subject.required') }}
                </BFormInvalidFeedback>
            </BFormGroup>
            <BFormGroup id="create-cta-form-description" :label="t('components.createCallToAction.description.label')">
                <BFormTextarea
                    min="0"
                    rows="6"
                    v-model="description"
                    :maxlength="descriptionMaxLength"
                    :placeholder="t('components.createCallToAction.description.placeholder')"
                    @input="inputInvalid.description = false"
                />
                <small>{{ characterCount }}</small>
                <BFormInvalidFeedback :state="!inputInvalid.description">
                    <i class="icon icon--error-warning" />
                    {{ t('components.createCallToAction.description.required') }}
                </BFormInvalidFeedback>
            </BFormGroup>
            <div id="create-cta-form-actions">
                <BButton variant="link" @click="$emit('cancel')">
                    {{ t('components.createCallToAction.actions.cancel') }}
                </BButton>
                <BButton type="submit" variant="primary" :disabled="!enableButton">
                    {{ t('components.createCallToAction.actions.submit') }}
                </BButton>
            </div>
        </fieldset>
    </BForm>
</template>

<script lang="ts">
import type { CallToActionRequest } from '@dbco/portal-api/callToAction.dto';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import { Role } from '@dbco/portal-api/user';
import { ResourcePermissionV1, ChoreResourceTypeV1 } from '@dbco/enum';
import { useI18n } from 'vue-i18n-composable';
import useStatusAction, { isIdle, isRejected, isResolved } from '@/store/useStatusAction';
import showToast from '@/utils/showToast';
import { useStore } from '@/utils/vuex';
import type { PropType } from 'vue';
import { computed, defineComponent, ref, unref, watchEffect } from 'vue';

export default defineComponent({
    props: {
        caseUuid: { type: String as PropType<string>, required: true },
        token: { type: String as PropType<string> },
    },
    emits: ['cancel', 'created'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const characterCount = computed(() => `${description.value.length}/${descriptionMaxLength.value}`);
        const description = ref<string>('');
        const descriptionMaxLength = ref<string>('5000');
        const roles = ref<Array<Role>>([Role.user]);
        const selectedDate = ref<string>('');
        const selectedRole = ref<Role>(Role.user);
        const inputInvalid = ref<Record<string, boolean>>({
            date: false,
            description: false,
            role: false,
            subject: false,
        });
        const subject = ref<string>('');
        const allInputsValid = () => {
            const inputInvalidValue = unref(inputInvalid);
            inputInvalidValue.date = !selectedDate.value?.length;
            inputInvalidValue.description = !description.value?.length;
            inputInvalidValue.role = !selectedRole.value?.length;
            inputInvalidValue.subject = !subject.value?.length;
            return Object.values(inputInvalidValue).every((invalid) => invalid === false);
        };

        const { status: createStatus, action: createCallToAction } = useStatusAction(
            async (payload: CallToActionRequest, token?: string) => {
                await useCallToActionStore().createCallToAction(payload, token);
            }
        );

        watchEffect(() => {
            if (isResolved(createStatus.value)) {
                const toastMessage = `${t('pages.createCallToAction.success')} ${t('roles.user_alt')}`;
                showToast(toastMessage, 'create-cta-toast');
                emit('created');
            }
        });
        const enableButton = computed(() => isIdle(createStatus.value) || isRejected(createStatus.value));

        const submit = async () => {
            if (!allInputsValid()) return;
            const meta = useStore().getters['index/meta'];
            const expiresAt = new Date(selectedDate.value);
            expiresAt.setHours(23, 59, 59); // 1 sec to midnight so expiry happens on date itself

            await createCallToAction(
                {
                    subject: subject.value,
                    // eslint-disable-next-line no-warning-comments
                    organisation_uuid: (meta as any).organisationUuid, // TODO: Remove any cast, prop does not exists?!
                    resource_uuid: props.caseUuid,
                    resource_type: ChoreResourceTypeV1.VALUE_covid_case,
                    resource_permission: ResourcePermissionV1.VALUE_edit,
                    expires_at: expiresAt.toISOString(),
                    description: description.value,
                    role: selectedRole.value,
                },
                props.token
            );
        };

        return {
            characterCount,
            description,
            descriptionMaxLength,
            inputInvalid,
            enableButton,
            selectedDate,
            selectedRole,
            subject,
            submit,
            roles,
            t,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

form > fieldset {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr 1fr;
    font-weight: 500;

    > legend {
        font-weight: 700;
        margin-bottom: $padding-md;
    }
    input,
    textarea {
        border: $border-default;
        color: $black;
        font-size: 0.875rem;

        &::placeholder {
            color: $dark-grey;
        }
    }

    input {
        height: 2.75rem;
    }

    textarea {
        height: 15rem;
    }

    @media (max-width: ($breakpoint-lg - 1)) {
        display: flex;
        flex-direction: column;
    }

    ::v-deep {
        .b-dropdown {
            &.placeholder {
                button {
                    color: $dark-grey;
                }
            }
            button {
                border: $border-default;
                color: $black;
                padding: $padding-xs $padding-sm;
                background: none;
            }
            ul {
                width: 100%;
                max-height: 12.5rem;
                overflow: auto;
            }
            .dropdown-item {
                padding: $padding-xs $padding-sm;
                color: $black;
            }
        }
    }

    .invalid-feedback {
        margin-top: $padding-xs;
        i {
            margin-left: 0;
        }
    }

    #create-cta-form-description {
        grid-column: 1;
    }

    #create-cta-form-actions {
        display: flex;
        gap: $padding-xs;
        grid-column: 1 / span 2;
        justify-content: flex-end;
    }
}
</style>
