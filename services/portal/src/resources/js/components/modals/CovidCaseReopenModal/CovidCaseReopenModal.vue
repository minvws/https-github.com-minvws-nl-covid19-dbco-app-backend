<template>
    <BModal
        v-if="cases && cases.length"
        :title="title"
        ok-title="Case heropenen"
        ok-variant="primary"
        cancel-title="Annuleren"
        cancel-variant="outline-primary"
        @ok.prevent="onConfirm"
        @hidden="resetModal"
        ref="modal"
    >
        <p>Goed om te weten: als je een case heropent, wordt deze actie opgeslagen.</p>
        <BForm>
            <BFormGroup label="Waarom wil je case heropenen?" label-for="reopen-text-area">
                <BFormTextarea
                    v-model="reopenNote"
                    id="reopen-text-area"
                    placeholder="Vul een toelichting in"
                    rows="3"
                    @input="showRequiredMessage = false"
                />
                <BFormInvalidFeedback class="invalid-feedback" :state="!showRequiredMessage">
                    <i class="icon icon--error-warning" />
                    Leg uit waarom je de case wilt heropenen
                </BFormInvalidFeedback>
            </BFormGroup>
            <BFormGroup label="Toewijzing" label-for="reopen-assignment">
                <AssignmentDropdown
                    :title="reopenAssigneeTitle"
                    id="reopen-assignment"
                    v-if="assignable"
                    :block="true"
                    :uuids="caseUuids"
                    :staleSince="''"
                    @optionSelected="assignOptionSelected"
                />
            </BFormGroup>
        </BForm>
    </BModal>
</template>

<script lang="ts">
import type { BModal } from 'bootstrap-vue';
import { caseApi } from '@dbco/portal-api';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { AssignmentResult } from '@dbco/portal-api/assignment';
import AssignmentDropdown from '@/components/utils/AssignmentDropdown/AssignmentDropdown.vue';
import showToast from '@/utils/showToast';
import { usePlanner } from '@/store/planner/plannerStore';
import { mapActions } from 'pinia';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';

export default defineComponent({
    name: 'CovidCaseReopenModal',
    components: {
        AssignmentDropdown,
    },
    props: {
        assigneeTitle: {
            default: 'Niet toegewezen',
            required: false,
        },
        cases: {
            type: Array as PropType<PlannerCaseListItem[]>,
            required: true,
        },
    },
    data() {
        return {
            newAssignment: undefined as string | undefined,
            reopenNote: '',
            reopenRequestSuccessful: false,
            showRequiredMessage: false,
            toastId: 'reopen-toast',
        };
    },
    computed: {
        assignable() {
            return !this.cases?.some((c) => c.isAssignable === false);
        },
        caseUuids() {
            return this.cases?.map((c) => c.uuid);
        },
        reopenAssigneeTitle() {
            return this.newAssignment ?? this.assigneeTitle;
        },
        successMessage() {
            return this.cases?.length === 1
                ? `Case ${this.cases[0].caseId} is heropend`
                : 'Meerdere cases zijn heropend';
        },
        toastMessage() {
            return this.reopenRequestSuccessful ? this.successMessage : 'Er is iets fout gegaan met het heropenen';
        },
        title() {
            return this.cases?.length === 1 ? `${this.cases[0].caseId} heropenen` : 'Meerdere cases heropenen';
        },
    },
    methods: {
        ...mapActions(usePlanner, ['changeAssignment']),
        assignOptionSelected(optionResult: AssignmentResult) {
            if (optionResult.option.type === 'option') {
                this.newAssignment = optionResult.option.label;
            }
        },
        async onConfirm() {
            if (!this.reopenNote.length) {
                this.showRequiredMessage = true;
                return;
            }
            await this.changeAssignment();
            try {
                await caseApi.reopenCases(this.caseUuids, this.reopenNote);
                this.reopenRequestSuccessful = true;
            } catch (error) {
                this.reopenRequestSuccessful = false;
            }
            showToast(this.toastMessage, this.toastId, !this.reopenRequestSuccessful);
            this.resetModal();
            this.$emit('reopenDone');
        },
        resetModal() {
            this.reopenNote = '';
            this.newAssignment = undefined;
            this.showRequiredMessage = false;
        },
        show() {
            (this.$refs.modal as BModal).show();
        },
        hide() {
            (this.$refs.modal as BModal).hide();
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.form-group {
    margin-bottom: 0;

    textarea {
        border: 1px solid $lightest-grey;
        color: $black;
        padding: 0.75rem 1rem;
        font-size: 0.875rem; // 14/16
    }
    .invalid-feedback {
        margin-top: 0.5rem;
        i {
            margin-left: 0;
            margin-right: 0.25rem;
        }
    }

    .b-dropdown {
        border: 1px solid $lightest-grey;
        border-radius: $border-radius-small;
        ::v-deep {
            .dropdown-toggle {
                color: $black !important;
                display: flex;
                align-items: center;
                justify-content: space-between;
                &:after {
                    color: $lightest-grey !important;
                }
            }
        }
    }
}

.form-group + .form-group {
    margin-top: 1rem;
}
</style>
