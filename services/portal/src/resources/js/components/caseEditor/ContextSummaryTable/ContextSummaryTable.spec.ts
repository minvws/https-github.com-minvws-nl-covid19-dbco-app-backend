import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import Vuex from 'vuex';

import * as CaseUtils from '@/utils/case';

import { contextApi } from '@dbco/portal-api';
import { createContainer, decorateWrapper, flushCallStack, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';

import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { VueConstructor } from 'vue';
import ContextSummaryTable from './ContextSummaryTable.vue';
import i18n from '@/i18n/index';
import type { Context } from '@dbco/portal-api/context.dto';

const simpleMockContext = {
    uuid: 'uuid',
    label: 'label',
    explanation: 'explanation',
    detailedExplanation: 'detailedExplanation',
    moments: ['2021-07-15'],
    isSource: false,
};

const mockGetContexts = (mockContextArray?: Array<any>) => {
    return vi.spyOn(contextApi, 'getContexts').mockImplementation(() => {
        const contexts: Array<Context> = [];
        mockContextArray?.forEach((mockContext) => {
            contexts.push({
                ...simpleMockContext,
                ...mockContext,
            });
        });
        if (!contexts.length) contexts.push(simpleMockContext);
        return Promise.resolve({ contexts });
    });
};

const indexState: Partial<IndexStoreState> = {
    uuid: '55e0d97d-119c-408e-9a8d-2588e068e456',
    fragments: {},
};

const props = {
    caseUuid: 'case-uuid',
};

const data = {
    contexts: [],
    loaded: true,
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props?: object,
        data: object = {},
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoState: Partial<UserInfoState> = {}
    ) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        return shallowMount(ContextSummaryTable, {
            localVue,
            i18n,
            data: () => data,
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: { BTr: false },
            attachTo: createContainer(), // supresses [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.
        });
    }
);

describe('ContextSummaryTable.vue', () => {
    it('should load number of rows according to the number of contexts', async () => {
        // GIVEN the api returns 3 contexts
        const mockedGetContexts = mockGetContexts([{}, {}, {}]);

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the component renders 3 contexts
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        expect(tableRows.length).toBe(3);
    });

    it('should show "geverifieerd" when context is verified', async () => {
        // GIVEN the api returns three contexts of which only the fourth has a verified place
        const mockedGetContexts = mockGetContexts([
            { place: {} },
            { place: undefined },
            { place: { isVerified: false } },
            { place: { isVerified: true } },
        ]);

        // WHEN the component renders the contexts
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the component shows the first three contexts not to be verified, as they aren't
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        expect(tableRows.at(0).find('.verified').exists()).toBe(false);
        expect(tableRows.at(1).find('.verified').exists()).toBe(false);
        expect(tableRows.at(2).find('.verified').exists()).toBe(false);
        // AND it does show that the fourth context is verified, as it's place is indeed verified
        expect(tableRows.at(3).find('.verified').exists()).toBe(true);
    });

    it('should render "datum ontbreekt" when a context has no moments', async () => {
        // GIVEN the api returns a context without moments
        const mockedGetContexts = mockGetContexts([{ moments: [] }]);

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the table row renders the missing dates alert
        const tableRow = wrapper.findComponent({ name: 'BTbody' }).findComponent({ name: 'BTr' });
        expect(decorateWrapper(tableRow).findByTestId('missing-dates-alert').text()).toBe('Datum ontbreekt');
    });

    it('should use infectiousDates for its datesSource property', async () => {
        // GIVEN infectiousDates returns a start date which is in 2001
        const mockedInfectiousDates = vi.spyOn(CaseUtils, 'infectiousDates').mockImplementation(() => {
            return { startDate: new Date('2021-01-10'), endDate: new Date() }; // 10 Jan
        });
        // AND the component renders
        const wrapper = createComponent(props, data, indexState);
        await wrapper.vm.$nextTick();

        // WHEN the datesSource property is accessed
        wrapper.vm.datesSource;

        // THEN the infectiousDates was called with the case fragments
        expect(mockedInfectiousDates).toHaveBeenCalledWith(indexState.fragments);
    });

    it('should render proper label given moments and fragments', async () => {
        // GIVEN infectiousDates returns a start date which is in 2001
        const mockedInfectiousDates = vi.spyOn(CaseUtils, 'infectiousDates').mockImplementation(() => {
            return { startDate: new Date('2021-01-10'), endDate: new Date() }; // 10 Jan
        });
        // AND sourceDates returns a end date which is in 2003
        const mockedSourceDates = vi.spyOn(CaseUtils, 'sourceDates').mockImplementation(() => {
            return { startDate: new Date(), endDate: new Date('2021-01-20') }; // 20 Jan
        });
        // AND the api returns three contexts, which are before, in between and after the previous dates
        const contexts = [
            { moments: ['2021-01-05'] }, //  5 Jan : before both
            { moments: ['2021-01-15'] }, // 15 Jan : before source & after infectious
            { moments: ['2021-01-25'] }, // 20 Jan : after both
        ];
        const mockedGetContexts = mockGetContexts(contexts);

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the sourceDates was called with the case fragments
        expect(mockedSourceDates).toHaveBeenCalledWith(indexState.fragments);
        // AND the infectiousDates was called with the case fragments
        expect(mockedInfectiousDates).toHaveBeenCalledWith(indexState.fragments);
        // AND the table row renders moments label correctly for each
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        expect(tableRows.at(0).find('.moments-label').text()).toBe('Bron');
        expect(tableRows.at(1).find('.moments-label').text()).toBe('Bron & Besmettelijk');
        expect(tableRows.at(2).find('.moments-label').text()).toBe('Besmettelijk');
    });

    it('should render label "Nog geen datums ingevuld" when no infectious date and source dates can be determined', async () => {
        // GIVEN infectious dates cannot be determined
        const mockedInfectiousDates = vi.spyOn(CaseUtils, 'infectiousDates').mockImplementation(() => null);
        // AND sourceDates cannot be determined
        const mockedSourceDates = vi.spyOn(CaseUtils, 'sourceDates').mockImplementation(() => null);
        // AND the api returns a context with a moment
        const mockedGetContexts = mockGetContexts();

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the sourceDates was called with the case fragments
        expect(mockedSourceDates).toHaveBeenCalledWith(indexState.fragments);
        // AND the infectiousDates was called with the case fragments
        expect(mockedInfectiousDates).toHaveBeenCalledWith(indexState.fragments);
        // AND the table row renders moments label with "Nog geen datums ingevuld"
        const tableRow = wrapper.findComponent({ name: 'BTbody' }).findComponent({ name: 'BTr' });
        expect(tableRow.find('.moments-label').text()).toBe('Nog geen datums ingevuld');
    });

    it('should not render the modal when a row is selected with a context without uuid', async () => {
        // GIVEN the api returns a contexts without uuid
        mockGetContexts([{ uuid: undefined }]);
        // AND the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // WHEN the row is clicked
        wrapper.vm.selectContext(wrapper.vm.contexts[0]);
        await wrapper.vm.$nextTick();

        // THEN the ContextEditingModal is not shown
        expect(wrapper.findComponent({ name: 'ContextEditingModal' }).exists()).toBe(false);
    });

    it('should render the modal when the row is selected and the contex has a uuid', async () => {
        // GIVEN the api returns a context with a uuid
        mockGetContexts();
        // AND the component renders
        const wrapper = createComponent(props, data, indexState);
        await flushCallStack();

        // WHEN the row is clicked
        await wrapper.vm.selectContext(wrapper.vm.contexts[0]);
        await flushCallStack();

        // THEN the ContextEditingModal is shown
        expect(wrapper.findComponent({ name: 'ContextEditingModal' }).exists()).toBe(true);
    });

    it('should hide the modal when it throws an on close event and reload contexts', async () => {
        // GIVEN the api returns a context with a uuid
        const mockedGetContexts = mockGetContexts();
        // AND the component renders
        const wrapper = createComponent(props, data, indexState);
        await wrapper.vm.$nextTick();
        // AND the row is selected
        wrapper.vm.selectContext(wrapper.vm.contexts[0]);
        await wrapper.vm.$nextTick();
        // AND the modal is shown
        expect(wrapper.findComponent({ name: 'ContextEditingModal' }).exists()).toBe(true);

        // WHEN the modal emits an onclose event
        mockedGetContexts.mockClear();
        wrapper.vm.deselectContext();
        await wrapper.vm.$nextTick();

        // THEN the api was called with the case uuid
        expect(mockedGetContexts).toHaveBeenCalledWith(props.caseUuid);
        // AND the modal is hidden
        expect(wrapper.findComponent({ name: 'ContextEditingModal' }).exists()).toBe(false);
    });

    it('should format the postal code for easy pasting into HPZone', async () => {
        // GIVEN the api returns a context with a uuid
        mockGetContexts();
        // AND the component renders
        const wrapper = createComponent(props, data, indexState);
        await wrapper.vm.$nextTick();

        // WHEN the component formats a series of badly written postal codes
        const badlyWrittenPostalCodes = [
            undefined,
            '1000',
            'AA',
            '100 AA',
            '1000AA',
            '1000 AA',
            '1000                 AA',
            'foo',
        ];
        const formattedPostalCodes = badlyWrittenPostalCodes.map((wrapper as any).vm.formatPostalCode);

        // THEN the postal codes are formatted nicely, non-postal codes are returned as they were
        expect(formattedPostalCodes).toStrictEqual([
            undefined,
            '1000',
            'AA',
            '100 AA',
            '1000 AA',
            '1000 AA',
            '1000 AA',
            'foo',
        ]);
    });
});
