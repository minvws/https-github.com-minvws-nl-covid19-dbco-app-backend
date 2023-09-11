<template>
    <div>
        <BModal
            visible
            title="Weet je zeker dat je de status wil updaten?"
            :okTitle="okTitle"
            @hide="hide"
            @ok.prevent="submit"
        >
            <FormInfo
                class="info-block--lg mb-4"
                text="Eventuele toewijzingen aan BCO'ers worden ingetrokken."
                infoType="warning"
            />
            <BForm @submit.prevent="submit" class="form">
                <BFormGroup label="Nieuwe case teruggeven">
                    <BFormRadioGroup v-model="selectedStatus" stacked required>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_not_started">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_not_started] }}
                        </BFormRadio>
                    </BFormRadioGroup>
                </BFormGroup>

                <BFormGroup label="Teruggeven">
                    <BFormRadioGroup v-model="selectedStatus" stacked required>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_two_times_not_reached">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_two_times_not_reached] }}
                        </BFormRadio>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_callback_request">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_callback_request] }}
                        </BFormRadio>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_loose_end">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_loose_end] }}
                        </BFormRadio>
                    </BFormRadioGroup>
                </BFormGroup>

                <BFormGroup label="Afronden">
                    <BFormRadioGroup v-model="selectedStatus" stacked required>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_four_times_not_reached">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_four_times_not_reached] }}
                        </BFormRadio>
                        <BFormRadio class="badge-radio" :value="ContactTracingStatus.VALUE_bco_finished">
                            {{ contactTracingOptions[ContactTracingStatus.VALUE_bco_finished] }}
                        </BFormRadio>
                    </BFormRadioGroup>
                </BFormGroup>

                <BFormGroup v-if="canApproveCase" label="Wat moet er met deze case gebeuren?">
                    <BFormRadioGroup
                        v-model="casequalityFeedback"
                        :options="casequalityFeedbackOptions"
                        stacked
                        required
                        @change="showCaseQualityFeedbackError = false"
                    >
                        <BFormInvalidFeedback class="invalid-feedback" :state="!showCaseQualityFeedbackError">
                            <i class="icon icon--error-warning" />
                            Kies eerst wat er met de case moet gebeuren
                        </BFormInvalidFeedback>
                    </BFormRadioGroup>
                </BFormGroup>

                <BFormGroup label="Toelichting">
                    <BFormTextarea
                        v-model="statusExplanation"
                        placeholder="Leg uit wat er nog moet gebeuren. Deel hier geen persoonsgegevens."
                        rows="3"
                        max-rows="6"
                    />
                    <BFormInvalidFeedback
                        :class="showExplanationRequiredError ? 'invalid-feedback' : 'required-feedback'"
                        :state="!showExplanationRequiredMessage"
                    >
                        <i class="icon icon--error-warning" />
                        Dit veld is nog leeg. Een toelichting is vereist.
                    </BFormInvalidFeedback>
                </BFormGroup>
            </BForm>
        </BModal>

        <BModal
            id="confirm-modal"
            title="Weet je zeker dat je de case wilt afronden?"
            okTitle="Afronden"
            :visible="showConfirmModal"
            @ok="process"
            @hide="showConfirmModal = false"
        >
            <FormInfo class="mb-3" text="Je kunt de case niet meer bewerken zonder hulp van een werkverdeler." />
        </BModal>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { FormField } from '@/components/form/ts/formTypes';
import {
    ContactTracingStatusV1,
    contactTracingStatusV1Options,
    PermissionV1,
    BcoStatusV1,
    CasequalityFeedbackV1,
    casequalityFeedbackV1Options,
} from '@dbco/enum';
import type { BvModalEvent } from 'bootstrap-vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { StoreType } from '@/store/storeType';
import { IndexStoreAction } from '@/store/index/indexStoreAction';
import axios from 'axios';
import { mapActions, mapGetters } from '@/utils/vuex';

export default defineComponent({
    name: 'OsirisCaseStatus',
    components: { FormInfo },
    props: {
        covidCase: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            casequalityFeedback: null as CasequalityFeedbackV1 | null,
            casequalityFeedbackOptions: Object.entries(casequalityFeedbackV1Options).map((item) => {
                return { value: item[0], text: item[1] };
            }) as FormField[],
            contactTracingOptions: contactTracingStatusV1Options,
            ContactTracingStatus: ContactTracingStatusV1,
            selectedStatus: ContactTracingStatusV1.VALUE_not_started,
            showCaseQualityFeedbackError: false,
            showConfirmModal: false,
            showExplanationRequiredError: false,
            statusExplanation: '',
        };
    },
    computed: {
        ...mapGetters(StoreType.INDEX, ['statusIndexContactTracing']),
        canApproveCase(): boolean {
            if (!this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_caseApprove)) return false;

            // This check is because in case edit screen as user it gets its data from the store so its nested in meta
            if (this.covidCase.meta) {
                if (this.covidCase.meta?.bcoStatus !== BcoStatusV1.VALUE_completed) return false;
            } else {
                // As planner its a normal Plannercase
                if (this.covidCase.bcoStatus !== BcoStatusV1.VALUE_completed) return false;
            }

            return true;
        },
        finishedStatus() {
            switch (this.selectedStatus) {
                case ContactTracingStatusV1.VALUE_bco_finished:
                case ContactTracingStatusV1.VALUE_four_times_not_reached:
                    return true;
                default:
                    return false;
            }
        },
        okTitle() {
            if (this.canApproveCase && this.casequalityFeedback) {
                return [CasequalityFeedbackV1.VALUE_reject_and_reopen, CasequalityFeedbackV1.VALUE_complete].includes(
                    this.casequalityFeedback
                )
                    ? 'Case teruggeven'
                    : 'Case sluiten';
            } else {
                return this.finishedStatus ? 'Afronden' : 'Indienen';
            }
        },
        showExplanationRequiredMessage() {
            if (this.statusExplanation.length) return false;
            switch (this.selectedStatus) {
                case ContactTracingStatusV1.VALUE_two_times_not_reached:
                case ContactTracingStatusV1.VALUE_callback_request:
                case ContactTracingStatusV1.VALUE_loose_end:
                    return true;
                default:
                    return false;
            }
        },
    },
    created() {
        if (this.statusIndexContactTracing && this.statusIndexContactTracing != 'unknown') {
            this.selectedStatus = this.statusIndexContactTracing;
        }
    },
    methods: {
        ...mapActions(StoreType.INDEX, { updateContactStatus: IndexStoreAction.UPDATE_CONTACT_STATUS }),
        async process() {
            try {
                await this.updateContactStatus({
                    uuid: this.covidCase.uuid,
                    statusIndexContactTracing: this.selectedStatus,
                    statusExplanation: this.statusExplanation,
                    casequalityFeedback: this.canApproveCase ? this.casequalityFeedback : null,
                });
                this.$emit('cancel');
                if (this.finishedStatus) {
                    this.$modal.show({
                        title: 'De case is afgerond',
                        okOnly: true,
                        onConfirm: () => {
                            window.location.replace('/');
                        },
                    });
                } else {
                    this.$modal.show({
                        title: 'De case is teruggegeven',
                        text: 'De werkverdeler gaat ermee aan de slag.',
                        okOnly: true,
                        onConfirm: () => {
                            window.location.replace('/');
                        },
                    });
                }
            } catch (error) {
                if (axios.isAxiosError(error) && error.message) {
                    this.$modal.show({
                        title: error.message,
                        okOnly: true,
                    });
                }
            }
        },
        submit() {
            if (this.canApproveCase && !this.casequalityFeedback) {
                this.showCaseQualityFeedbackError = true;
                return;
            }
            if (this.showExplanationRequiredMessage && !this.statusExplanation.length) {
                this.showExplanationRequiredError = true;
                return;
            }
            const feedbackOptionsRequireConfirmation =
                this.casequalityFeedback !== CasequalityFeedbackV1.VALUE_reject_and_reopen;

            if (this.canApproveCase && feedbackOptionsRequireConfirmation) {
                this.showConfirmModal = true;
                return;
            }

            void this.process();
        },
        hide({ trigger }: BvModalEvent) {
            // Emit cancel on all possible ways to close the modal (only way to catch ESC), except confirming
            if (trigger === 'ok') return;

            this.$emit('cancel');
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

::v-deep {
    .form-group {
        margin-bottom: 1.5rem;
        legend {
            color: $black;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .custom-control-label {
            padding-top: 2px;
        }
        .badge-radio + .badge-radio {
            margin-top: 0.5rem;
        }
        .invalid-feedback,
        .required-feedback {
            margin-top: 0.5rem;
            i {
                margin-left: 0;
                margin-right: 0.25rem;
            }
        }
        .required-feedback {
            font-size: inherit;
            color: $black;
            i {
                background-color: $bco-orange;
            }
        }
    }
    .form-group:last-child {
        margin-bottom: 0;
    }
}
</style>
