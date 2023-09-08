import { shallowMount } from '@vue/test-utils';
import { Store } from 'vuex';
import contextStore from '@/store/context/contextStore';
import organisationStore from '@/store/organisation/organisationStore';
import ContextEditingModal from './ContextEditingModal.vue';
import mockOfRootSchemaStub from '@/components/form/ts/__stubs__/rootSchema';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { createTestingPinia } from '@pinia/testing';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { addDays, subDays } from 'date-fns';
import indexStore from '@/store/index/indexStore';
import { FixedCalendarPeriodV1 } from '@dbco/enum';

vi.mock('@dbco/portal-api/client/context.api', () => ({
    getFragments: vi.fn(() => Promise.resolve({})),
}));

vi.mock('@/components/form/ts/formSchema', () => ({
    getRootSchema: vi.fn(() => mockOfRootSchemaStub),
    getSchema: vi.fn(() => []),
}));

const createComponent = setupTest(
    (localVue: VueConstructor, context: object = {}, data: object = {}, visitDate = '') => {
        const mockContextStoreFragments = {
            general: {
                moments: [{ day: visitDate }],
            },
        };

        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
            },
        };

        return shallowMount<ContextEditingModal>(ContextEditingModal, {
            data: () => data,
            localVue,
            propsData: {
                context,
            },
            store: new Store({
                modules: {
                    index: indexStoreModule,
                    context: {
                        ...contextStore,
                        getters: {
                            fragments: vi.fn(() => mockContextStoreFragments),
                        },
                        state: {
                            ...contextStore.state,
                            ...{
                                fragments: mockContextStoreFragments,
                            },
                        },
                    },
                    organisation: {
                        ...organisationStore,
                        state: organisationStore.state,
                    },
                },
            }),
            pinia: createTestingPinia(),
        });
    }
);

describe('ContextEditingModal.vue', () => {
    it('should have "Context" as default title', () => {
        // WHEN the context editing modal renders
        const wrapper = createComponent();

        // THEN the modal title should be "Context"
        expect(wrapper.vm.title).toBe('Context');
    });

    it('should have "Broncontext" as title when context is in source range', () => {
        // GIVEN a context visit
        const visit = new Date();

        // AND a currently active source date range
        const givenSourceRange = {
            startDate: subDays(visit, 2),
            endDate: addDays(visit, 2),
        };

        // WHEN the context editing modal renders
        const wrapper = createComponent(undefined, undefined, visit);
        const calendar = useCalendarStore(wrapper.vm.$pinia);
        // @ts-ignore:next-line
        vi.spyOn(calendar, 'getCalendarDateItemsByKey').mockImplementationOnce(() => {
            return {
                [FixedCalendarPeriodV1.VALUE_source]: givenSourceRange,
            };
        });

        // THEN the modal title should be "Broncontext"
        expect(wrapper.vm.title).toBe('Broncontext');
    });

    it('should have "Context binnen besmettelijke periode" as title when context is in contagious range', () => {
        // GIVEN a context visit
        const visit = new Date();

        // AND a currently active contagious date range
        const givenContagiousRange = {
            startDate: subDays(visit, 2),
            endDate: addDays(visit, 2),
        };

        // WHEN the context editing modal renders
        const wrapper = createComponent(undefined, undefined, visit);
        const calendar = useCalendarStore(wrapper.vm.$pinia);
        // @ts-ignore:next-line
        vi.spyOn(calendar, 'getCalendarDateItemsByKey').mockImplementationOnce(() => {
            return {
                [FixedCalendarPeriodV1.VALUE_contagious]: givenContagiousRange,
            };
        });

        // THEN the modal title should be "Context binnen besmettelijke periode"
        expect(wrapper.vm.title).toBe('Context binnen besmettelijke periode');
    });

    it('should have "Broncontext & context binnen besmettelijke periode" as title when context is in both ranges', () => {
        // GIVEN a context visit
        const visit = new Date();

        // AND a currently active source date range
        const givenSourceRange = {
            startDate: subDays(visit, 2),
            endDate: addDays(visit, 2),
        };

        // AND a currently active contagious date range
        const givenContagiousRange = {
            startDate: subDays(visit, 2),
            endDate: addDays(visit, 2),
        };

        // WHEN the context editing modal renders
        const wrapper = createComponent(undefined, undefined, visit);
        const calendar = useCalendarStore(wrapper.vm.$pinia);
        // @ts-ignore:next-line
        vi.spyOn(calendar, 'getCalendarDateItemsByKey').mockImplementationOnce(() => {
            return {
                [FixedCalendarPeriodV1.VALUE_source]: givenSourceRange,
                [FixedCalendarPeriodV1.VALUE_contagious]: givenContagiousRange,
            };
        });

        // THEN the modal title should be "Broncontext & context binnen besmettelijke periode"
        expect(wrapper.vm.title).toBe('Broncontext & context binnen besmettelijke periode');
    });
});
