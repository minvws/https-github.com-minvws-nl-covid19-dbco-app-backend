import { caseMetricsApi } from '@dbco/portal-api';
import type { CasesCreatedArchivedMetric, CasesCreatedArchivedResponse } from '@dbco/portal-api/caseMetrics.dto';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { createFakeDataCollectionGenerator, createFakeDataGenerator } from '@/utils/__fakes__/createFakeDataGenerator';
import { Tbody, Tr } from '@dbco/ui-library';
import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import type { VueConstructor } from 'vue';
import CaseMetricsPage from './CaseMetricsPage.vue';
import { createTestingPinia } from '@pinia/testing';

vi.mock('@dbco/portal-api/client/caseMetrics.api', () => ({
    getList: vi.fn(() => Promise.resolve({ refreshedAt: null, eTag: null, data: [] } as CasesCreatedArchivedResponse)),
    refresh: vi.fn(() => Promise.resolve()),
}));

const caseMetric = createFakeDataGenerator<CasesCreatedArchivedMetric>(() => ({
    date: fakerjs.date.past().toISOString(),
    created: fakerjs.number.int(),
    archived: fakerjs.number.int(),
}));

const caseMetricCollection = createFakeDataCollectionGenerator(caseMetric);

const createComponent = setupTest((localVue: VueConstructor) => {
    return mount(CaseMetricsPage, {
        localVue,
        pinia: createTestingPinia(),
        stubs: {
            Heading: true,
            TableContainer: true,
            HStack: true,
        },
    });
});

const date = new Date(2022, 0, 10);

beforeAll(() => {
    vi.useFakeTimers();
    vi.setSystemTime(date);
});

afterAll(() => {
    vi.useRealTimers();
});

describe('CaseMetricsPage.vue', () => {
    it('should start loading the metrics on mount', () => {
        const wrapper = createComponent();

        expect(caseMetricsApi.getList).toHaveBeenCalledOnce();
        expect(wrapper.findByTestId('loading').exists()).toBe(true);
    });

    it('should show a message when there are no results', async () => {
        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.findByTestId('loading').exists()).toBe(false);
        expect(wrapper.findByTestId('no-results').exists()).toBe(true);
    });

    it('should display the metrics', async () => {
        const numResults = fakerjs.number.int({ max: 100, min: 10 });
        (caseMetricsApi.getList as Mock).mockImplementationOnce(() =>
            Promise.resolve({
                refreshedAt: null,
                data: caseMetricCollection(numResults),
            } as CasesCreatedArchivedResponse)
        );
        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRows = wrapper.findComponent(Tbody).findAllComponents(Tr);
        expect(tableBodyRows.length).toBe(numResults);
    });

    it.each([
        [null, 'Laatste berekening - -'],
        ['2022-01-11T13:01:57', 'Laatste berekening - 11 jan. 2022 om 13:01'],
        ['2022-01-10T13:01:57', 'Laatste berekening - 13:01'],
        ['2022-01-09T13:01:57', 'Laatste berekening - Gisteren om 13:01'],
        ['2022-01-08T13:01:57', 'Laatste berekening - Eergisteren om 13:01'],
        ['2022-01-07T13:01:57', 'Laatste berekening - 07 jan. 2022 om 13:01'],
    ])('should display the last refresh date', async (refreshedAt, expectedString) => {
        (caseMetricsApi.getList as Mock).mockImplementationOnce(() =>
            Promise.resolve({
                refreshedAt,
                eTag: null,
                data: [],
            } as CasesCreatedArchivedResponse)
        );

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.findByTestId('refreshed-at').text()).toEqual(expectedString);
    });

    it('should refresh case metrics api on click', async () => {
        const wrapper = createComponent();
        await wrapper.findByTestId('refreshButton').trigger('click');
        await flushCallStack();
        expect(caseMetricsApi.refresh).toHaveBeenCalledOnce();
        wrapper.vm.$destroy();
    });

    it('should start polling the metrics on click', async () => {
        const eTag = fakerjs.string.alpha(32);
        (caseMetricsApi.getList as Mock).mockImplementationOnce(() =>
            Promise.resolve({
                refreshedAt: fakerjs.date.recent().toISOString(),
                eTag,
                data: [],
            } as CasesCreatedArchivedResponse)
        );
        const wrapper = createComponent();
        (caseMetricsApi.getList as Mock).mockClear();
        await wrapper.findByTestId('refreshButton').trigger('click');
        await flushCallStack();
        expect(caseMetricsApi.getList).toHaveBeenCalledWith(eTag, expect.anything());
        wrapper.vm.$destroy();
    });

    it('should keep previous metrics when api returns null', async () => {
        (caseMetricsApi.getList as Mock).mockImplementationOnce(() =>
            Promise.resolve({
                refreshedAt: fakerjs.date.recent().toISOString(),
                eTag: fakerjs.string.alpha(32),
                data: [],
            } as CasesCreatedArchivedResponse)
        );
        const wrapper = createComponent();
        await flushCallStack();

        (caseMetricsApi.getList as Mock).mockImplementationOnce(() => Promise.resolve(null));
        await wrapper.findByTestId('refreshButton').trigger('click');
        await flushCallStack();
        expect(caseMetricsApi.getList).toHaveBeenCalled();
        expect(wrapper.findByTestId('refreshed-at').text()).toEqual('Het verversen kan enkele minuten duren...');
        wrapper.vm.$destroy();
    });
});
