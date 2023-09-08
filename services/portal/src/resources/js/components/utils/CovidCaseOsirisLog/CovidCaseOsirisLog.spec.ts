import { fakerjs, flushCallStack, setupTest } from '@/utils/test';

import { mount } from '@vue/test-utils';
import CovidCaseOsirisLog from './CovidCaseOsirisLog.vue';

import { caseApi } from '@dbco/portal-api';
import { faker } from '@faker-js/faker';
import type { VueConstructor } from 'vue';
import i18n from '@/i18n/index';
import { fakeLogItem } from '@/utils/__fakes__/osirisLog';
import type { OsirisLogItem } from '@dbco/portal-api/osiris.dto';
import { formatDate, parseDate } from '@/utils/date';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getOsirisLog: vi.fn(() => Promise.resolve([])),
}));

describe('CovidCaseOsirisLog.vue', () => {
    const createComponent = setupTest((localVue: VueConstructor, propsData?: Record<string, any>) => {
        return mount<CovidCaseOsirisLog>(CovidCaseOsirisLog, {
            localVue,
            i18n,
            propsData: {
                caseOsirisNumber: fakerjs.number.int(),
                caseUuid: faker.string.uuid(),
                ...propsData,
            },
            stubs: {
                BSpinner: true,
            },
        });
    });

    it('should show message when finished loading and no log items found', async () => {
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() => Promise.resolve([]));

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.html()).toContain(i18n.t('components.covidCaseOsirisLog.no_items'));
    });

    it('should render given log item', async () => {
        const givenLogItem = fakeLogItem();
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() =>
            Promise.resolve([givenLogItem as OsirisLogItem])
        );

        // WHEN the component renders the log
        const wrapper = createComponent();
        await flushCallStack();

        const logItems = wrapper.findAll('.log-item');

        expect(logItems.at(0).html()).toContain(i18n.t(`components.covidCaseOsirisLog.titles.${givenLogItem.status}`));
        expect(logItems.at(0).html()).toContain(formatDate(parseDate(givenLogItem.time), 'd MMMM yyyy HH:mm'));
    });

    it('should render given log item with "case reopened" description', async () => {
        const givenLogItem = fakeLogItem({ caseIsReopened: true });
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() =>
            Promise.resolve([givenLogItem as OsirisLogItem])
        );

        // WHEN the component renders the log
        const wrapper = createComponent();
        await flushCallStack();

        const logItems = wrapper.findAll('.log-item');

        expect(logItems.at(0).html()).toContain(i18n.t('components.covidCaseOsirisLog.reopened'));
    });

    it('should render given log item when osiris number is null / not available', async () => {
        const givenLogItem = fakeLogItem();
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() =>
            Promise.resolve([givenLogItem as OsirisLogItem])
        );

        // WHEN the component renders the log
        const wrapper = createComponent({ caseOsirisNumber: null });
        await flushCallStack();

        expect(wrapper.find('.form-heading').text()).toContain('Osiris (Meldnummer: nog niet bekend)');
        expect(wrapper.find('.note-description').text()).toContain('Meldnummer RIVM: nog niet bekend');
    });

    it('should render given log item with validation errors if osirisValidationResponse is not null', async () => {
        const givenLogItem = fakeLogItem();
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() =>
            Promise.resolve([givenLogItem as OsirisLogItem])
        );

        // WHEN the component renders the log
        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.findAllByTestId('validation-response').length).toBe(3);
    });

    it('should render given log item without validation errors if osirisValidationResponse is null', async () => {
        const givenLogItem = fakeLogItem({ osirisValidationResponse: null });
        vi.spyOn(caseApi, 'getOsirisLog').mockImplementationOnce(() =>
            Promise.resolve([givenLogItem as OsirisLogItem])
        );

        // WHEN the component renders the log
        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.findAllByTestId('validation-response').length).toBe(0);
    });
});
