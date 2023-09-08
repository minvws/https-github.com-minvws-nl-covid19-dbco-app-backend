import { createLocalVue, shallowMount } from '@vue/test-utils';
import AccessRequestsTable from './AccessRequestsTable.vue';
import BootstrapVue from 'bootstrap-vue';
import { accessRequestApi } from '@dbco/portal-api';
import type { AccessRequestOverviewResponse } from '@dbco/portal-api/accessRequest.dto';
import { flushCallStack } from '@/utils/test';

describe('AccessRequestsTable.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);

    const setWrapper = (data: object = {}, accessRequests: AccessRequestOverviewResponse[] = []) => {
        vi.spyOn(accessRequestApi, 'getOverview').mockImplementationOnce(() =>
            Promise.resolve({ count: accessRequests.length, data: accessRequests })
        );

        return shallowMount(AccessRequestsTable, {
            localVue,
            stubs: {
                InfiniteLoading: true,
            },
            data: () => data,
            mocks: {
                $filters: {
                    truncate: vi.fn((x) => x),
                    dateFormat: vi.fn((x) => x),
                },
            },
        });
    };

    it('should render loader when loading', () => {
        const wrapper = setWrapper({ isLoading: true });

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('.icon--spinner').exists()).toBe(true);
    });

    it('should correctly render single access request items', async () => {
        const wrapper = setWrapper({}, [
            {
                caseUuid: '001',
                date: '2021-01-01',
                name: 'name',
                user: 'user',
                show: 1,
                exported: 0,
                caseDeleted: 0,
                caseDeleteStarted: 0,
                caseDeleteRecovered: 0,
                contactDeleted: 0,
                contactDeleteStarted: 0,
                contactDeleteRecovered: 0,
            },
        ]);

        await flushCallStack();

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('h4').text()).toEqual('1 resultaat');

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows).toHaveLength(1);

        expect(tableRows.at(0).html()).toContain('2021-01-01');
        expect(tableRows.at(0).html()).toContain('name');
        expect(tableRows.at(0).html()).toContain('user');
    });

    it('should correctly render list of access request items', async () => {
        const wrapper = setWrapper({}, [
            {
                caseUuid: '001',
                date: '2021-01-01',
                name: 'name',
                user: 'user',
                show: 1,
                exported: 0,
                caseDeleted: 0,
                caseDeleteStarted: 0,
                caseDeleteRecovered: 0,
                contactDeleted: 0,
                contactDeleteStarted: 0,
                contactDeleteRecovered: 0,
            },
            {
                caseUuid: '002',
                date: '2021-01-01',
                name: 'name',
                user: 'user',
                show: 1,
                exported: 0,
                caseDeleted: 0,
                caseDeleteStarted: 0,
                caseDeleteRecovered: 0,
                contactDeleted: 0,
                contactDeleteStarted: 0,
                contactDeleteRecovered: 0,
            },
            {
                caseUuid: '003',
                date: '2021-01-01',
                name: 'name',
                user: 'user',
                show: 1,
                exported: 0,
                caseDeleted: 0,
                caseDeleteStarted: 0,
                caseDeleteRecovered: 0,
                contactDeleted: 0,
                contactDeleteStarted: 0,
                contactDeleteRecovered: 0,
            },
        ]);

        await flushCallStack();

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('h4').text()).toEqual('3 resultaten');

        const tableRows = wrapper.findAll('tbody > tr');

        expect(tableRows).toHaveLength(3);

        tableRows.wrappers.forEach((row) => {
            expect(row.html()).toContain('2021-01-01');
            expect(row.html()).toContain('name');
            expect(row.html()).toContain('user');
        });
    });

    it.each([
        ['bekijken', ['show']],
        ['downloaden', ['exported']],
        ['verwijderen gestart', ['caseDeleteStarted']],
        ['herstellen', ['caseDeleteRecovered']],
        ['verwijderen gestart', ['contactDeleteStarted']],
        ['herstellen', ['contactDeleteRecovered']],
        ['bekijken, downloaden', ['show', 'exported']],
        ['bekijken, downloaden, herstellen', ['show', 'exported', 'caseDeleteRecovered']],
    ])('%#: should show action string "%s" for actions "%s"', async (expectedActionString, actions) => {
        // setup an accessRequest item with all actions
        const exampleAccessRequest = {
            caseUuid: '001',
            date: '2021-01-01',
            name: 'name',
            user: 'user',
            show: actions.some((a) => a === 'show') ? 1 : 0,
            exported: actions.some((a) => a === 'exported') ? 1 : 0,
            caseDeleted: 0,
            caseDeleteStarted: actions.some((a) => a === 'caseDeleteStarted') ? 1 : 0,
            caseDeleteRecovered: actions.some((a) => a === 'caseDeleteRecovered') ? 1 : 0,
            contactDeleted: 0,
            contactDeleteStarted: actions.some((a) => a === 'contactDeleteStarted') ? 1 : 0,
            contactDeleteRecovered: actions.some((a) => a === 'contactDeleteRecovered') ? 1 : 0,
        };

        const wrapper = setWrapper({}, [exampleAccessRequest]);
        await flushCallStack();
        const expectedActionsString = expectedActionString[0].toUpperCase() + expectedActionString.slice(1);
        expect(wrapper.find('[data-testid="actions"]').text()).toEqual(expectedActionsString);
    });
});
