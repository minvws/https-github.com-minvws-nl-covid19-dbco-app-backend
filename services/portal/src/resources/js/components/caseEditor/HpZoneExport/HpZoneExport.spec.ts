import { shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';
import HpZoneExport from './HpZoneExport.vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { flushCallStack, setupTest } from '@/utils/test';
import { copyApi } from '@dbco/portal-api';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { VueConstructor } from 'vue';

const diagnosticsHtml = '<b>Diagnostics</b>';
const eventsHtml = '<b>Events</b>';

vi.mock('@dbco/portal-api/client/copy.api', () => ({
    getDiagnostics: vi.fn(() => Promise.resolve(diagnosticsHtml)),
    getEvents: vi.fn(() => Promise.resolve(eventsHtml)),
}));

vi.mock('@/utils/copy', () => ({
    copyHtmlToClipboard: vi.fn(() => Promise.resolve()),
}));

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        caseUuid: string,
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

        return shallowMount(HpZoneExport, {
            localVue,
            data: () => data,
            propsData: {
                caseUuid,
            },
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
        });
    }
);

describe('HpZoneExport.vue', () => {
    it('should render the diagnostics HTML as HTML', () => {
        const wrapper = createComponent('1234', { diagnosticsTableHtml: diagnosticsHtml });

        expect(wrapper.find('.diagnostics-table').element.innerHTML).toBe(diagnosticsHtml);
    });

    it('should reload the diagnostics after a change in the store', async () => {
        const wrapper = createComponent('1234', undefined, {
            fragments: {
                communication: {
                    conditionalAdviceGiven: 'a',
                },
            },
        });
        await flushCallStack();

        const otherHtml = '<b>Other HTML</b>';
        vi.spyOn(copyApi, 'getDiagnostics').mockImplementationOnce(() => Promise.resolve(otherHtml));

        expect(wrapper.find('.diagnostics-table').element.innerHTML).toBe(diagnosticsHtml);

        // Change the store
        await wrapper.vm.$store.dispatch('index/CHANGE', {
            path: 'fragments',
            values: {
                communication: {
                    otherAdviceGiven: 'b',
                },
            },
        });

        // Await mocked API call
        await flushCallStack();

        expect(wrapper.find('.diagnostics-table').element.innerHTML).toBe(otherHtml);
    });
});
