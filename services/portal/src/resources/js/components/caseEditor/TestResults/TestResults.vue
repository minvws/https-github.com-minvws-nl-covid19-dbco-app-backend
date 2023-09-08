<template>
    <div class="tw-relative">
        <div
            data-testid="loading-indicator"
            class="tw-absolute tw-w-full tw-h-full tw-bg-white tw-bg-opacity-50 tw-flex tw-justify-center tw-items-center tw-gap-2"
            v-if="isLoading"
        >
            <BSpinner small label="Laden test resultaten" />
            <span>Test resultaten worden geladen...</span>
        </div>
        <ul v-if="testResults && testResults.length > 0">
            <li v-for="testResult in testResults" :key="testResult.uuid" :class="{ open: isToggled(testResult.uuid) }">
                <div class="tw-flex tw-w-full">
                    <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
                    <header @click="toggleItem(testResult.uuid)">
                        <ChevronRightIcon class="svg-icon" />
                        <span class="result-title">
                            {{ resultLabels[testResult.result] }}
                            {{ labelForTestResultType(testResult) }}
                        </span>
                        <span>Testdatum: {{ dateFormatMonth(testResult.dateOfTest) }}</span>
                    </header>
                    <button
                        v-if="testResult.source === TestResultSourceV1.VALUE_manual"
                        type="button"
                        class="tw-border-none tw-bg-transparent tw-group tw-pr-4"
                        data-testid="testresults-btn-delete"
                        :disabled="!userCanEdit"
                        @click="() => onDelete(testResult)"
                    >
                        <img :src="iconDeleteSvg" alt="Delete" class="tw-opacity-50 group-hover:tw-opacity-100" />
                    </button>
                </div>
                <div v-if="isToggled(testResult.uuid)" class="result-details">
                    <table>
                        <tr>
                            <td>Bronsysteem</td>
                            <td>{{ testResultSourceV1Options[testResult.source] }}</td>
                        </tr>
                        <tr>
                            <td>Datum testresultaat</td>
                            <td data-testid="date-of-result">
                                {{
                                    testResult.dateOfResult
                                        ? $filters.dateFnsFormat(testResult.dateOfResult, 'd MMMM yyyy')
                                        : '-'
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td>Datum melding bij GGD</td>
                            <td>{{ $filters.dateFnsFormat(testResult.receivedAt, 'd MMMM yyyy') }}</td>
                        </tr>
                        <tr>
                            <td>Meldende instelling</td>
                            <td data-testid="test-location">
                                {{ testResult.testLocation ? testResult.testLocation : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>GGD Testlocatie</td>
                            <td data-testid="sample-location">
                                {{ testResult.sampleLocation ? testResult.sampleLocation : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Monsternummer</td>
                            <td data-testid="monster-number">
                                {{ testResult.sampleNumber ? testResult.sampleNumber : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Laboratorium</td>
                            <td data-testid="laboratory">
                                {{ testResult.laboratory ? testResult.laboratory : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </li>
        </ul>
        <p iv v-else-if="!isLoading" class="py-5 text-center">Er zijn (nog) geen testuitslagen bekend.</p>
        <div class="tw-py-6 tw-px-8">
            <BButton
                variant="secondary"
                type="button"
                @click="toggleModal"
                data-testid="add-test-result-button"
                :disabled="!userCanEdit"
                ><span class="tw-text-sm">&#65291; Test toevoegen</span></BButton
            >
        </div>
        <TestResultCreateModal
            v-if="isModalVisible"
            @save="loadTestResults"
            @cancel="() => toggleModal(false)"
            :case="uuid"
        />
    </div>
</template>

<script lang="ts">
import { useToggleArray } from '@/composables/useToggleArray';
import { useFilters } from '@/filters/useFilters';
import { IndexStoreAction } from '@/store/index/indexStoreAction';
import { StoreType } from '@/store/storeType';
import { computed, defineComponent, onMounted, ref } from 'vue';
import {
    TestResultSourceV1,
    testResultSourceV1Options,
    TestResultTypeOfTestV1,
    testResultTypeOfTestV1Options,
    TestResultV1,
} from '@dbco/enum';
import { useStore } from '@/utils/vuex';
import ChevronRightIcon from '@icons/chevron-right.svg?vue';
import TestResultCreateModal from '@/components/caseEditor/TestResultCreateModal/TestResultCreateModal.vue';
import useStatusAction, { isPending } from '@/store/useStatusAction';
import type { TestResult } from '@dbco/portal-api/case.dto';
import { useModal } from '@/components/AppHooks';
import iconDeleteSvg from '@images/icon-delete.svg';
import { userCanEdit as userCanEditFn } from '@/utils/interfaceState';
import { caseApi } from '@dbco/portal-api';

const resultLabels: Record<TestResultV1, string> = {
    [TestResultV1.VALUE_negative]: 'Negatieve',
    [TestResultV1.VALUE_positive]: 'Positieve',
    [TestResultV1.VALUE_unknown]: 'Onbekende',
};
export default defineComponent({
    name: 'TestResults',
    components: { TestResultCreateModal, ChevronRightIcon },
    setup() {
        const isModalVisible = ref(false);
        const modal = useModal();
        const { dateFormatMonth } = useFilters();
        const { isToggled, toggleItem } = useToggleArray();
        const { getters, dispatch } = useStore();
        const uuid = computed(() => getters[`${StoreType.INDEX}/uuid`]);
        const testResults = computed(() => getters[`${StoreType.INDEX}/testResults`]);
        const userCanEdit = computed(userCanEditFn);

        const labelForTestResultType = ({ typeOfTest, customTypeOfTest }: TestResult) => {
            if (typeOfTest === TestResultTypeOfTestV1.VALUE_unknown) {
                return 'testuitslag (type onbekend)';
            }
            if (typeOfTest === TestResultTypeOfTestV1.VALUE_custom) {
                return `testuitslag (type anders${customTypeOfTest ? `: ${customTypeOfTest}` : ''})`;
            }
            return testResultTypeOfTestV1Options.find(({ value }) => value === typeOfTest)?.label;
        };
        const { action: loadTestResults, status } = useStatusAction(async () => {
            await dispatch(`${StoreType.INDEX}/${IndexStoreAction.GET_TEST_RESULTS}`);
        });
        const toggleModal = (show = !isModalVisible.value) => {
            isModalVisible.value = show;
        };

        onMounted(async () => {
            await loadTestResults();
        });

        const onDelete = (test: TestResult) =>
            modal.show({
                title: 'Weet je zeker dat je het testresultaat wil verwijderen?',
                text: 'Let op: je kunt dit hierna niet meer ongedaan maken',
                okTitle: 'Verwijderen',
                okVariant: 'outline-danger',
                onConfirm: () => {
                    void deleteTestResult(test);
                },
            });

        const { status: deleteStatus, action: deleteTestResult } = useStatusAction(async (testResult: TestResult) => {
            await caseApi.deleteTestResult(uuid.value, testResult.uuid);
            await loadTestResults();
        });
        const isLoading = computed(() => isPending(deleteStatus.value) || isPending(status.value));

        return {
            dateFormatMonth,
            isToggled,
            toggleItem,
            toggleModal,
            labelForTestResultType,
            loadTestResults,
            onDelete,
            isModalVisible,
            testResults,
            uuid,
            isLoading,
            resultLabels,
            testResultSourceV1Options,
            TestResultSourceV1,
            iconDeleteSvg,
            userCanEdit,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/variables.scss';

ul {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
        border-bottom: 1px solid $lightest-grey;

        header {
            display: flex;
            align-items: center;
            padding: $padding-sm;
            flex-grow: 1;
            cursor: pointer;

            .result-title {
                font-weight: bold;
                flex-grow: 1;
            }
        }

        .result-details {
            border-top: 1px solid $lightest-grey;
            padding: 0 $padding-md;
            background-color: $even-lighter-grey;

            table {
                width: 100%;

                tr {
                    border-bottom: 1px solid $lightest-grey;

                    td {
                        padding: 1rem 0;
                        width: 50%;
                    }

                    &:last-child {
                        border-bottom: none;
                    }
                }
            }
        }

        .svg-icon {
            display: inline-block;
            height: 1.5rem;
            margin-right: 0.25rem;
        }

        &.open {
            .svg-icon {
                transform: rotate(90deg);
            }
        }

        &:last-child {
            border-bottom: none;
        }
    }
}
</style>
