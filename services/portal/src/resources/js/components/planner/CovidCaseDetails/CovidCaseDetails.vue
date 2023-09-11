<template>
    <div class="form-container">
        <table class="table form-container">
            <tbody>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.hp_zone') }}</th>
                    <td>{{ selectedCase.hpzoneNumber || '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.test_monster_number') }}</th>
                    <td>{{ selectedCase.testMonsterNumber || '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.portal_number') }}</th>
                    <td>{{ selectedCase.caseId || '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.date_of_birth') }}</th>
                    <td>{{ age(selectedCase.dateOfBirth) || '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.priority') }}</th>
                    <td>
                        <BFormSelect
                            v-model="selectedCase.priority"
                            @change="debouncedPersistMeta('priority', selectedCase.priority)"
                            :options="priorities"
                            :disabled="selectedCase.plannerView === PlannerView.COMPLETED || selectedCaseIsArchived"
                        />
                    </td>
                </tr>
                <tr v-if="!selectedCaseIsArchived">
                    <th>{{ $t('components.covidCaseDetails.titles.label') }}</th>
                    <td>
                        <FormulateInput
                            type="formMultiSelectDropdown"
                            placeholder="Geen labels"
                            v-model="selectedCaseLabels"
                            :listOptions="
                                caseLabels.map((label) => {
                                    return { ...label, value: label.uuid };
                                })
                            "
                            :disabled="selectedCase.plannerView === PlannerView.COMPLETED"
                            :filterEnabled="true"
                            class="w100"
                        />
                    </td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.date_of_test') }}</th>
                    <td>
                        {{
                            selectedCase.dateOfTest
                                ? formatDate(parseDate(selectedCase.dateOfTest, 'yyyy-MM-dd'), 'dd-MM-yyyy')
                                : '-'
                        }}
                    </td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.created_at') }}</th>
                    <td>{{ formatDate(parseDate(selectedCase.createdAt), 'd MMMM yyyy HH:mm') }}</td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.test_result_source') }}</th>
                    <td>{{ formattedTestResultSource(selectedCase) }}</td>
                </tr>
                <tr v-if="showAssignDropdown">
                    <th>{{ $t('components.covidCaseDetails.titles.assignment') }}</th>
                    <td>
                        <DbcoAssignDropdown
                            v-if="selectedCase.isAssignable"
                            :uuid="[selectedCase.uuid]"
                            :title="assigneeTitle"
                            :toggleClass="
                                selectedCase.assignedUser ||
                                (selectedCase.assignedOrganisation && !selectedCase.assignedOrganisation.isCurrent) ||
                                selectedCase.assignedCaseList
                                    ? 'text-muted'
                                    : 'text-primary'
                            "
                            :staleSince="staleSince"
                            @optionSelected="assignOptionSelected"
                        />
                        <span v-else>{{ assigneeTitle }}</span>
                    </td>
                </tr>
                <tr>
                    <th>{{ $t('components.covidCaseDetails.titles.updated_at') }}</th>
                    <td>{{ formatDate(parseDate(selectedCase.updatedAt), 'd MMMM yyyy HH:mm') }}</td>
                </tr>
            </tbody>
        </table>
        <div class="py-4">
            <h3>{{ $t('components.covidCaseDetails.titles.notes') }}</h3>
            <FormulateInput
                type="textarea"
                :placeholder="`${$t('components.covidCaseDetails.hints.note_placeholder')}`"
                v-model="caseNote"
                class="w100"
                maxlength="5000"
            />
            <BButton variant="primary" @click="addCaseNote" :disabled="caseNote === ''">
                {{ $t('components.covidCaseDetails.actions.add_note') }}
            </BButton>
            <span v-if="caseNoteAdded" class="ml-2">
                <i class="icon icon--success icon--m0" /> {{ $t('components.covidCaseDetails.hints.note_added') }}
            </span>
        </div>
    </div>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import type { CaseLabel, PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import DbcoAssignDropdown from '@/components/formControls/DbcoAssignDropdown/DbcoAssignDropdown.vue';
import { usePlanner } from '@/store/planner/plannerStore';
import { BcoStatusV1, CaseNoteTypeV1, priorityV1Options, testResultSourceV1Options } from '@dbco/enum';
import { calculateAge, formatDate, parseDate } from '@/utils/date';
import { mapState } from 'pinia';
import { defineComponent } from 'vue';
import type { CaseCreateUpdate, CaseUpdateMeta } from '@dbco/portal-api/case.dto';
import { updatePlannerCase, updatePlannerCaseMeta } from '@dbco/portal-api/client/case.api';

export default defineComponent({
    name: 'CovidCaseDetails',
    components: {
        DbcoAssignDropdown,
    },
    props: {
        assigneeTitle: {
            type: String,
            required: false,
        },
    },
    data() {
        return {
            PlannerView,
            caseNote: '',
            caseNoteAdded: false,
            selectedCaseLabels: [] as string[],
            staleSince: '',
            testResultSources: testResultSourceV1Options,
        };
    },
    created() {
        this.selectedCaseLabels = this.selectedCase.caseLabels.map((label: CaseLabel) => label.uuid);
        this.staleSince = formatDate(new Date(), 'yyyy-MM-dd HH:mm:ss');
    },
    computed: {
        ...mapState(usePlanner, { caseLabels: 'caseLabels', maybeSelectedCase: 'selectedCase' }),
        priorities() {
            return Object.entries(priorityV1Options).map(([value, text]) => ({ value, text }));
        },
        selectedCase() {
            // I think this should be a prop. When the details are open, there is always a selected case.
            if (!this.maybeSelectedCase) throw Error('Case must be selected');
            return this.maybeSelectedCase;
        },
        selectedCaseIsArchived() {
            return this.selectedCase.bcoStatus === BcoStatusV1.VALUE_archived;
        },
        showAssignDropdown() {
            return !!this.assigneeTitle && this.selectedCase.bcoStatus !== BcoStatusV1.VALUE_archived;
        },
        uuid() {
            return this.selectedCase.uuid;
        },
    },
    watch: {
        async selectedCaseLabels() {
            if (
                // Every label in this.selectedCaseLabels may not be present in this.selectedCase.caseLabels
                !this.selectedCaseLabels.every((uuid) =>
                    this.selectedCase.caseLabels.find((caseLabel) => caseLabel.uuid == uuid)
                ) ||
                // Local this.selectedCaseLabels must differ from this.selectedCase.caseLabels
                this.selectedCaseLabels.length !== this.selectedCase.caseLabels.length
            ) {
                await this.debouncedPersistMeta('caseLabels', this.selectedCaseLabels);
            }
        },
    },
    methods: {
        parseDate,
        formatDate,
        async debouncedPersist(name: keyof CaseCreateUpdate, value?: string | string[] | null) {
            if (!this.selectedCase.isEditable) return;
            const props: Partial<CaseCreateUpdate> = { [name]: value };
            const {
                data: { caseLabels },
            } = await updatePlannerCase(this.uuid, props);

            this.selectedCase.caseLabels = caseLabels;
        },
        async debouncedPersistMeta(name: keyof CaseUpdateMeta, value?: string | string[] | null) {
            const props: Partial<CaseUpdateMeta> = { [name]: value };
            const {
                data: { caseLabels },
            } = await updatePlannerCaseMeta(this.uuid, props);

            this.selectedCase.caseLabels = caseLabels;
        },
        async addCaseNote() {
            if (this.caseNote !== '') {
                await caseApi.addCaseNote(this.uuid, this.caseNote, CaseNoteTypeV1.VALUE_case_note);
                this.caseNote = '';
                this.caseNoteAdded = true;
                setTimeout(() => (this.caseNoteAdded = false), 3000);
            }
        },
        age(value: string | null) {
            if (!value) return null;
            return calculateAge(new Date(value));
        },
        assignOptionSelected() {
            this.$root.$emit('caseUpdated');
        },
        formattedTestResultSource(item: PlannerCaseListItem) {
            if (!item.testResults?.length) return '-';
            if (item.testResults.every((result, index, array) => result === array[0]))
                return this.testResultSources[item.testResults[0]];
            return this.$t('shared.test_result_source_multiple');
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.table {
    min-height: 0;
    margin: 0;

    tbody {
        tr {
            &:first-child {
                th,
                td {
                    border-top: none;
                }
            }

            th {
                font-weight: normal;
                vertical-align: middle;
                width: 40%;
            }

            td {
                color: $black;
                word-break: break-word;

                ::v-deep {
                    .formulate-input-element--formMultiSelectDropdown {
                        max-width: 100%;
                    }
                }
            }
        }
    }
}
</style>
