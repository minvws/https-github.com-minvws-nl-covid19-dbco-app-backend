import { createLocalVue, shallowMount } from '@vue/test-utils';
import CovidCaseOverviewUserPage from './CovidCaseOverviewUserPage.vue';
import userInfoStore from '@/store/userInfo/userInfoStore';
import Vuex from 'vuex';
import BootstrapVue from 'bootstrap-vue';
import { PermissionV1 } from '@dbco/enum';
import { PiniaVuePlugin } from 'pinia';
import { createTestingPinia } from '@pinia/testing';
import type { UntypedWrapper } from '@/utils/test';
import FiltersPlugin from '@/plugins/filters';

vi.mock('@/env');
import env from '@/env';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    assignNextCaseInQueue: vi.fn((queue: string) => {
        if (queue === 'default') return Promise.reject();
        return Promise.resolve({ caseUuid: '245284d0-5ac6-4dac-b7fe-4be4230bb5f5' });
    }),
    getCaseLabels: vi.fn(() => Promise.resolve({})),
    updateAssignment: vi.fn(() => Promise.resolve({})),
}));

// We need a window object that let's us set the url in the test. normally this isn't available during runs.
// Using this code this object will be used and we can look at what gets passed to the href.
global.window = Object.create(window);
const url = 'http://dummy.com';
Object.defineProperty(window, 'location', {
    value: {
        href: url,
    },
});

describe('CovidCaseOverviewUserPage.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(PiniaVuePlugin);
    localVue.use(FiltersPlugin);

    const setWrapper = (data: Record<string, unknown> = {}, userInfoState: Record<string, unknown> = {}) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
            $refs: {
                newcaseform: HTMLDivElement,
            },
        };

        return shallowMount(CovidCaseOverviewUserPage, {
            localVue,
            data: () => data,
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
            pinia: createTestingPinia({
                stubActions: false,
            }),
            stubs: {
                CovidCaseUserTable: true,
                FormCase: true,
            },
        }) as UntypedWrapper;
    };

    it('should render the component', () => {
        const storeData = {
            hasPermission: [],
        };

        const wrapper = setWrapper(undefined, storeData);

        expect(wrapper.find('span').text()).toContain('Mijn Cases');
    });

    it('should display "Case aanmaken" button if the permission for creating cases is present', () => {
        const storeData = {
            permissions: PermissionV1.VALUE_caseCreate,
        };

        env.isAddCaseButtonUserEnabled = true;
        const wrapper = setWrapper(undefined, storeData);

        expect(wrapper.find('bbutton-stub[data-testid="caseAanmaken"]').text()).toContain('＋ Case aanmaken');
    });

    it('should call the newcaseform.open() onclick on the "case aanmaken" button', async () => {
        const storeData = {
            permissions: PermissionV1.VALUE_caseCreate,
        };

        env.isAddCaseButtonUserEnabled = true;
        const wrapper = setWrapper(undefined, storeData);

        wrapper.vm.$refs.newcaseform.open = vi.fn();
        const spy = vi.spyOn(wrapper.vm.$refs.newcaseform, 'open');

        await wrapper.find('bbutton-stub[data-testid="caseAanmaken"]').trigger('click');

        expect(spy).toHaveBeenCalledTimes(1);
    });

    it('should display "Case oppakken" button if the user has the permission to pick up new cases', () => {
        const storeData = {
            permissions: PermissionV1.VALUE_caseCanPickUpNew,
        };

        env.isAddCaseButtonUserEnabled = true;
        const wrapper = setWrapper(undefined, storeData);

        expect(wrapper.find('bbutton-stub[data-testid="caseOppakken"]').text()).toContain('＋ Case oppakken');
    });

    it('should change the window location to "/editcase/uuid" onclick on the "case oppakken" button, when there are cases in the queue', async () => {
        const storeData = {
            permissions: PermissionV1.VALUE_caseCanPickUpNew,
        };

        env.isAddCaseButtonUserEnabled = true;
        const wrapper = setWrapper(undefined, storeData);

        await wrapper.setData({ queue: { caseUuid: '245284d0-5ac6-4dac-b7fe-4be4230bb5f5' } });

        await wrapper.find('bbutton-stub[data-testid="caseOppakken"]').trigger('click');
        await wrapper.vm.$nextTick();

        expect(window.location.href).toContain('/editcase/245284d0-5ac6-4dac-b7fe-4be4230bb5f5');
    });

    it('should display a modal when the user clicks on "case oppakken" when there are no cases in the queue', async () => {
        const storeData = {
            permissions: PermissionV1.VALUE_caseCanPickUpNew,
        };

        env.isAddCaseButtonUserEnabled = true;
        const wrapper = setWrapper(undefined, storeData);

        // I set the mock to throw on queue = 'default'

        const modalShowMock = vi.fn();
        wrapper.vm.$modal = { show: modalShowMock };

        await wrapper.find('bbutton-stub[data-testid="caseOppakken"]').trigger('click');
        await wrapper.vm.$nextTick();

        expect(modalShowMock).toHaveBeenCalledTimes(1);
    });
});
