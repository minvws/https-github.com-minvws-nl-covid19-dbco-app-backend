import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, flushCallStack, createContainer, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import i18n from '@/i18n/index';

import PlaceCasesVisitedTable from './PlaceCasesVisitedTable.vue';
import { usePlaceCasesStore } from '@/store/cluster/clusterStore';
import type { PlaceCaseStoreState } from '@/store/cluster/clusterStore';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { fakePlaceCase, fakePlaceCaseTable } from '@/utils/__fakes__/place';
import { assignmentApi } from '@dbco/portal-api';
import { YesNoUnknownV1, contextRelationshipV1Options } from '@dbco/enum';
import { subDays } from 'date-fns';
import { fakeCalendarDateRange } from '@/utils/__fakes__/calendarDateRange';

const createComponent = setupTest((localVue: VueConstructor, givenStoreState: PlaceCaseStoreState) => {
    return shallowMount<PlaceCasesVisitedTable>(PlaceCasesVisitedTable, {
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

describe('PlaceCasesVisitedTable.vue', () => {
    it('should load a table of content', async () => {
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
        expect(noMoreCasesMessage.text()).toBe(i18n.t('components.placeCasesTable.hints.all_index_cases_loaded'));
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
        expect(noMoreCasesMessage.text()).toBe(i18n.t('components.placeCasesTable.hints.no_index_cases'));
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
        const indexCase = wrapper.findAll('.custom-link').at(0);
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
        const indexCase = wrapper.findAll('.custom-link').at(0);
        await indexCase.trigger('click');

        await flushCallStack();

        // THEN redirect should NOT be called
        expect(windowOpenSpy).toHaveBeenCalledTimes(0);
    });

    it('should not render a given index name if notificationNamedConsent is false', () => {
        // GIVEN the component renders with table data
        const givenCase = { ...fakePlaceCase(undefined, false), ...{ notificationNamedConsent: false } };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        // THEN it should render the index name in the expected format
        const identifierInTable = wrapper.findAll('tbody td').at(0);
        const indexNameInTable = identifierInTable.find('p');
        const expectedName = '-';
        expect(indexNameInTable.text()).toBe(expectedName);
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
        const relationContextInTable = wrapper.findAll('tbody td').at(1);
        expect(relationContextInTable.text()).toBe(contextRelationshipV1Options[givenCase.relationContext]);
    });

    it('should correctly render the case date ranges', () => {
        // GIVEN the component renders with table data
        const givenCase = {
            ...fakePlaceCase(undefined, false),
            ...{
                moments: [
                    fakeCalendarDateRange({
                        startDate: subDays(new Date(), 1),
                        icon: 'diamond-grey',
                        label: 'unknown dates',
                    }),
                    fakeCalendarDateRange({
                        startDate: subDays(new Date(), 2),
                        icon: 'diamond-grey',
                        label: 'unknown dates',
                    }),
                    fakeCalendarDateRange({
                        startDate: subDays(new Date(), 3),
                        icon: 'diamond-grey',
                        label: 'unknown dates',
                    }),
                    fakeCalendarDateRange({
                        startDate: subDays(new Date(), 4),
                        icon: 'diamond-grey',
                        label: 'unknown dates',
                    }),
                    fakeCalendarDateRange({
                        startDate: subDays(new Date(), 32),
                        icon: 'diamond-grey',
                        label: 'unknown dates',
                    }),
                ],
            },
        };

        // WHEN the component is rendered
        const wrapper = createComponent({
            cases: [givenCase],
            table: fakePlaceCaseTable({}, false),
        });

        const infectionDates = wrapper.findAll('[data-testid="infection-date"]');
        expect(infectionDates.length).toBe(givenCase.moments.length - 1);
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
        const identifierInTable = wrapper.findAll('tbody td').at(0);
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
            const identifierInTable = wrapper.findAll('tbody td').at(0);
            const tooltipButton = identifierInTable.find('tooltipbutton-stub');
            expect(tooltipButton.exists()).toBe(renderIcon);
            if (renderIcon) {
                expect(tooltipButton.attributes('content')).toBe('Index maakt zich zorgen over de situatie op locatie');
                expect(tooltipButton.attributes('icon')).toBe('exclamation-mark-speech-bubble');
            }
        }
    );
});
