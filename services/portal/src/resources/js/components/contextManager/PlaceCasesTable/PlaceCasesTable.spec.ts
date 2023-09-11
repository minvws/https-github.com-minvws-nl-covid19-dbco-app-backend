import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, flushCallStack, createContainer, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import i18n from '@/i18n/index';

import PlaceCasesTable from './PlaceCasesTable.vue';
import { usePlaceCasesStore } from '@/store/cluster/clusterStore';
import type { PlaceCaseStoreState } from '@/store/cluster/clusterStore';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { fakePlaceCase, fakePlaceCaseTable } from '@/utils/__fakes__/place';
import { assignmentApi } from '@dbco/portal-api';
import { calculateAge, formatDate, parseDate } from '@/utils/date';
import { contextRelationshipV1Options, HospitalReasonV1, YesNoUnknownV1 } from '@dbco/enum';
import { fakeCalendarDateRange } from '@/utils/__fakes__/calendarDateRange';

const caseInTableNr = 0;
const ageInTableNr = 1;
const ezdInTableNr = 2;
const vaccinationInTableNr = 3;
const sectionsInTableNr = 5;
const relationContextInTableNr = 6;

const createComponent = setupTest((localVue: VueConstructor, givenStoreState: PlaceCaseStoreState) => {
    return shallowMount<PlaceCasesTable>(PlaceCasesTable, {
        localVue,
        i18n,
        attachTo: createContainer(),
        propsData: {
            placeUuid: fakerjs.string.uuid(),
        },
        pinia: createTestingPinia({
            initialState: {
                placeCases: givenStoreState,
            },
            stubActions: false,
        }),
    });
});

describe('PlaceCasesTable.vue', () => {
    it('should keep the infiniteLoader active if there are more pages to load', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            cases: [fakePlaceCase(undefined, false)],
            table: fakePlaceCaseTable({ lastPage: 3 }, false),
        });

        // WHEN the infinite loader loads a page that isn't the last one
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        vi.spyOn(usePlaceCasesStore(), 'fetchCases').mockImplementation(() => Promise.resolve());
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await flushCallStack();

        // THEN the infiniteLoader should still be active
        expect(stateChanger.loaded).toBeCalledTimes(1);
    });

    it('should stop the infiniteLoader when there are NO more pages to load', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            cases: [fakePlaceCase(undefined, false)],
            table: fakePlaceCaseTable({ lastPage: 1 }, false),
        });
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };

        // WHEN the infinite loader loads the last page
        vi.spyOn(usePlaceCasesStore(), 'fetchCases').mockImplementation(() => Promise.resolve());
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await flushCallStack();

        // THEN the infiniteLoader should be done
        expect(stateChanger.complete).toBeCalledTimes(1);
    });

    it('should show translated message when there are no more cases to load', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            cases: [fakePlaceCase(undefined, false)],
            table: fakePlaceCaseTable({ lastPage: 1 }, false),
        });
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };

        // WHEN the infinite loader loads the last page
        vi.spyOn(usePlaceCasesStore(), 'fetchCases').mockImplementation(() => Promise.resolve());
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await flushCallStack();

        // THEN a translated message should be shown
        const noMoreCasesMessage = wrapper.find('infiniteloading-stub').find('div:nth-child(2)');
        expect(noMoreCasesMessage.text()).toBe(
            i18n.t('components.placeCasesTable.hints.all_cases_loaded', { date: wrapper.vm.lastCaseDate })
        );
    });

    it('should show translated message when there are no cases', async () => {
        // GIVEN the component renders without cases
        const wrapper = createComponent({
            cases: [],
            table: fakePlaceCaseTable({ lastPage: 1 }, false),
        });
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };

        // WHEN the infinite loader loads without cases
        vi.spyOn(usePlaceCasesStore(), 'fetchCases').mockImplementation(() => Promise.resolve());
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await flushCallStack();

        // THEN a translated message should be shown
        const noMoreCasesMessage = wrapper.find('infiniteloading-stub').find('div:last-of-type');
        expect(noMoreCasesMessage.text()).toBe(i18n.t('components.placeCasesTable.hints.no_cases'));
    });

    it('lastCaseDate should have fallback for when there are no cases', async () => {
        // GIVEN no cases
        // WHEN the component renders
        const wrapper = createComponent({
            cases: [],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN the lastCaseDate should have a fallback
        expect(wrapper.vm.lastCaseDate).toBe('');
    });

    it('should redirect when a case click is successful', async () => {
        // GIVEN the component renders with table data
        const givenCase = fakePlaceCase(undefined, false);
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });
        const windowOpenSpy = vi.spyOn(window as any, 'open').mockImplementationOnce(() => vi.fn());
        vi.spyOn(assignmentApi, 'getAccessToCase').mockImplementationOnce(() => Promise.resolve());

        // WHEN a place is clicked
        const indexCase = wrapper.findAll('.custom-link').at(caseInTableNr);
        await indexCase.trigger('click');

        await flushCallStack();

        // THEN redirect should be called
        expect(windowOpenSpy).toHaveBeenCalled();
        expect(windowOpenSpy).toHaveBeenCalledWith(`/editcase/${givenCase.uuid}`, '_blank');
    });

    it('should do nothing when a case click is unsuccessful', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            cases: [fakePlaceCase(undefined, false)],
            table: fakePlaceCaseTable({}, false),
        });
        const windowOpenSpy = vi.spyOn(window as any, 'open').mockImplementationOnce(() => vi.fn());
        vi.spyOn(assignmentApi, 'getAccessToCase').mockImplementationOnce(() => Promise.reject());

        // WHEN a place is clicked
        const indexCase = wrapper.findAll('.custom-link').at(caseInTableNr);
        await indexCase.trigger('click');

        await flushCallStack();

        // THEN redirect should NOT be called
        expect(windowOpenSpy).toHaveBeenCalledTimes(0);
    });

    // elementType
    it.each([['a'], ['button'], ['i'], ['input'], ['label']])(
        'should NOT redirect when a %s element in the table is clicked',
        async (elementType) => {
            // GIVEN the component renders with table data
            const givenCase = fakePlaceCase(undefined, false);
            const wrapper = createComponent({
                cases: [givenCase],
                table: fakePlaceCaseTable({}, false),
            });
            const windowOpenSpy = vi.spyOn(window as any, 'open').mockImplementationOnce(() => vi.fn());

            // WHEN the elementType in the row is clicked
            const targetElement = document.createElement(elementType);
            await wrapper.vm.rowClicked(givenCase.uuid, givenCase.token, {
                target: targetElement,
            });

            // THEN redirect should NOT be called
            expect(windowOpenSpy).toHaveBeenCalledTimes(0);
        }
    );

    it('should not render a given index name if notificationNamedConsent is false', () => {
        // GIVEN the component renders with table data
        const givenCase = { ...fakePlaceCase(undefined, false), ...{ notificationNamedConsent: false } };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the index name in the expected format
        const identifierInTable = wrapper.findAll('tbody td').at(caseInTableNr);
        const indexNameInTable = identifierInTable.find('p');
        const expectedName = '-';
        expect(indexNameInTable.text()).toBe(expectedName);
    });

    it.each([
        {
            isDeceased: YesNoUnknownV1.VALUE_yes,
            renderIcon: true,
        },
        {
            isDeceased: YesNoUnknownV1.VALUE_no,
            renderIcon: false,
        },
        {
            isDeceased: YesNoUnknownV1.VALUE_unknown,
            renderIcon: false,
        },
        {
            isDeceased: null,
            renderIcon: false,
        },
        {
            isDeceased: undefined,
            renderIcon: false,
        },
    ])('should (only) render a tombstone icon when the index is deceased', ({ isDeceased, renderIcon }) => {
        // GIVEN the component renders with table data
        const givenCase = { ...fakePlaceCase(undefined, false), ...{ isDeceased } };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render a tombstone icon
        const identifierInTable = wrapper.findAll('tbody td').at(caseInTableNr);
        const tooltipButton = identifierInTable.find('tooltipbutton-stub');
        expect(tooltipButton.exists()).toBe(renderIcon);
        if (renderIcon) {
            expect(tooltipButton.attributes('content')).toBe('Index is overleden');
            expect(tooltipButton.attributes('icon')).toBe('tombstone');
        }
    });

    it.each([
        {
            causeForConcern: YesNoUnknownV1.VALUE_yes,
            renderIcon: true,
        },
        {
            causeForConcern: YesNoUnknownV1.VALUE_no,
            renderIcon: false,
        },
        {
            causeForConcern: YesNoUnknownV1.VALUE_unknown,
            renderIcon: false,
        },
        {
            causeForConcern: null,
            renderIcon: false,
        },
        {
            causeForConcern: undefined,
            renderIcon: false,
        },
    ])(
        'should (only) render an exclamation bubble icon when the index describes the context as a cause for concern',
        ({ causeForConcern, renderIcon }) => {
            // GIVEN the component renders with table data
            const givenCase = { ...fakePlaceCase(undefined, false), ...{ causeForConcern } };

            // WHEN the component is rendered
            const wrapper = createComponent({
                cases: [givenCase],
                table: fakePlaceCaseTable({}, false),
            });

            // THEN it should render an exclamation bubble icon
            const identifierInTable = wrapper.findAll('tbody td').at(caseInTableNr);
            const tooltipButton = identifierInTable.find('tooltipbutton-stub');
            expect(tooltipButton.exists()).toBe(renderIcon);
            if (renderIcon) {
                expect(tooltipButton.attributes('content')).toBe('Index maakt zich zorgen over de situatie op locatie');
                expect(tooltipButton.attributes('icon')).toBe('exclamation-mark-speech-bubble');
            }
        }
    );

    it('should render age based on dateOfBirth', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                dateOfBirth: fakerjs.date.birthdate().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the age from dateOfBirth in the expected format
        const dateOfBirthInTable = wrapper.findAll('tbody td').at(ageInTableNr);
        const age = calculateAge(new Date(givenCase.dateOfBirth));
        expect(dateOfBirthInTable.text()).toBe(age.toString());
    });

    it('should render a fall back with - if dateOfBirth is null', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                dateOfBirth: '',
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the age from dateOfBirth in the expected format
        const dateOfBirthInTable = wrapper.findAll('tbody td').at(1);
        expect(dateOfBirthInTable.text()).toBe('-');
    });

    it('should render a EZD date', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: fakerjs.date.recent().toISOString(),
                dateOfTest: fakerjs.date.past().toISOString(),
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the ezd date in the expected format
        const ezdDateInTable = wrapper.findAll('tbody td').at(ezdInTableNr);
        const date = formatDate(parseDate(givenCase.dateOfSymptomOnset, 'yyyy-MM-dd'), 'dd MMM yyyy');
        expect(ezdDateInTable.text()).toBe(date);
    });

    it('should render a fall back date of dateOfTest when there is no EZD date', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: fakerjs.date.past().toISOString(),
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the ezd date in the expected format
        const ezdDateInTable = wrapper.findAll('tbody td').at(ezdInTableNr);
        const date = formatDate(parseDate(givenCase.dateOfTest, 'yyyy-MM-dd'), 'dd MMM yyyy');
        expect(ezdDateInTable.text()).toBe(date);
    });

    it('should render a fall back to createdAt date when there is no EZD date', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                dateOfBirth: fakerjs.date.past().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });
        // THEN it should render the ezd date in the expected format
        const ezdDateInTable = wrapper.findAll('tbody td').at(ezdInTableNr);
        const date = formatDate(parseDate(givenCase.createdAt), 'dd MMM yyyy');
        expect(ezdDateInTable.text()).toBe(date);
    });

    it('should render a fall back with - if no count or date is found', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                vaccinationCount: null,
                mostRecentVaccinationDate: null,
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });
        // THEN it should render the ezd date in the expected format
        const vaccinationInTable = wrapper.findAll('tbody td').at(vaccinationInTableNr);
        expect(vaccinationInTable.text()).toBe('-');
    });

    it('should render only an mostRecentVaccinationDate if vaccinationCount is null with the fallback of -', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                vaccinationCount: null,
                mostRecentVaccinationDate: fakerjs.date.past().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });
        // THEN it should render the ezd date in the expected format
        const vaccinationInTable = wrapper.findAll('tbody td').at(vaccinationInTableNr);
        const date = formatDate(parseDate(givenCase.mostRecentVaccinationDate, 'yyyy-MM-dd'), 'dd MMM yyyy');
        expect(vaccinationInTable.text()).toBe(`- (${date})`);
    });

    it('should render only vaccinationCount if mostRecentVaccinationDate is null with the fallback of -', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                vaccinationCount: fakerjs.number.int({ min: 1, max: 2 }),
                mostRecentVaccinationDate: null,
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });
        // THEN it should render the ezd date in the expected format
        const vaccinationInTable = wrapper.findAll('tbody td').at(vaccinationInTableNr);
        expect(vaccinationInTable.text()).toBe(`${givenCase.vaccinationCount}x (-)`);
    });

    it('should render a vaccination count and date', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                caseId: fakerjs.string.uuid(),
                uuid: fakerjs.string.uuid(),
                token: fakerjs.string.uuid(),
                dateOfSymptomOnset: null,
                dateOfTest: null,
                moments: [fakeCalendarDateRange()],
                symptoms: {
                    hasSymptoms: YesNoUnknownV1.VALUE_yes,
                    stillHadSymptomsAt: fakerjs.date.past().toISOString(),
                },
                hospital: {
                    reason: HospitalReasonV1.VALUE_covid,
                    isAdmitted: YesNoUnknownV1.VALUE_yes,
                },
                createdAt: fakerjs.date.past().toISOString(),
                vaccinationCount: fakerjs.number.int({ min: 1, max: 2 }),
                mostRecentVaccinationDate: fakerjs.date.past().toISOString(),
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the ezd date in the expected format
        const vaccinationInTable = wrapper.findAll('tbody td').at(vaccinationInTableNr);
        const date = formatDate(parseDate(givenCase.mostRecentVaccinationDate, 'yyyy-MM-dd'), 'dd MMM yyyy');
        expect(vaccinationInTable.text()).toBe(`${givenCase.vaccinationCount}x (${date})`);
    });

    it('should render PlaceCaseSections', () => {
        // GIVEN the component renders with table data
        const givenCase = fakePlaceCase(undefined, false);

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the sections separated by commas
        const placeCaseSections = wrapper.find('placecasesections-stub');
        expect(placeCaseSections.exists()).toBe(true);
    });

    it('should render a fallback when there are no sections', () => {
        // GIVEN the component renders with table data
        const givenCase = { ...fakePlaceCase(undefined, false), ...{ sections: null } };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render a fallback
        const sectionsInTable = wrapper.findAll('tbody td').at(sectionsInTableNr);
        expect(sectionsInTable.text()).toBe('-');
    });

    it('should render a relation context', () => {
        // GIVEN the component renders with table data
        const givenCase = fakePlaceCase(undefined, false);

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the relationContext
        const relationContextInTable = wrapper.findAll('tbody td').at(relationContextInTableNr);
        expect(relationContextInTable.text()).toBe(contextRelationshipV1Options[givenCase.relationContext]);
    });
});
