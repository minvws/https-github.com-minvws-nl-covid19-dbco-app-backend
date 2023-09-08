import router from '@/router/router';
import type { Organisation, UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import CovidCaseOverviewPlannerPage from './CovidCaseOverviewPlannerPage.vue';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue, { BDropdown } from 'bootstrap-vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';

vi.mock('@/env');
import env from '@/env';
import TypingHelpers from '@/plugins/typings';

vi.mock('@dbco/portal-api/client/user.api', () => ({
    updateOrganisation: vi.fn(() =>
        Promise.resolve({
            uuid: '00000000-0000-0000-0000-000000000000',
            abbreviation: 'ORG',
            name: 'Demo ORG',
            hasOutsourceToggle: true,
            isAvailableForOutsourcing: true,
            bcoPhase: '1a',
            type: 'regional',
        })
    ),
}));
describe('CovidCaseOverviewPlannerPage', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(VueRouter);
    localVue.use(TypingHelpers);

    const setWrapper = (data: object = {}, userState: Partial<UserInfoState> = {}) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userState,
            },
        };

        return shallowMount(CovidCaseOverviewPlannerPage, {
            localVue,
            router,
            data: () => data,
            mocks: {
                // $route,
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: {
                BDropdown,
            },
        });
    };

    it('should hide title-bar if organisation.hasOutsourceToggle=false', () => {
        const wrapper = setWrapper(undefined, {
            organisation: { hasOutsourceToggle: false } as Organisation,
        });

        expect(wrapper.find('.title-bar').exists()).toBe(false);
    });

    it('should show title-bar if organisation.hasOutsourceToggle=true', () => {
        const wrapper = setWrapper(undefined, {
            organisation: { hasOutsourceToggle: true } as Organisation,
        });

        expect(wrapper.find('.title-bar').exists()).toBe(true);
    });

    it('should hide title-bar if env.isOutsourcingEnabled=false', () => {
        env.isOutsourcingEnabled = false;
        env.isOutsourcingToRegionalGGDEnabled = true;
        const wrapper = setWrapper(undefined, {
            organisation: { hasOutsourceToggle: true } as Organisation,
        });

        expect(wrapper.find('.title-bar').exists()).toBe(false);
    });

    it('should hide title-bar if env.isOutsourcingToRegionalGGDEnabled=false', () => {
        env.isOutsourcingEnabled = true;
        env.isOutsourcingToRegionalGGDEnabled = false;
        const wrapper = setWrapper(undefined, {
            organisation: { hasOutsourceToggle: true } as Organisation,
        });

        expect(wrapper.find('.title-bar').exists()).toBe(false);
    });

    it('should show title-bar if env.isOutsourcingEnabled=true && env.isOutsourcingToRegionalGGDEnabled=true', () => {
        env.isOutsourcingEnabled = true;
        env.isOutsourcingToRegionalGGDEnabled = true;
        const wrapper = setWrapper(undefined, {
            organisation: { hasOutsourceToggle: true } as Organisation,
        });

        expect(wrapper.find('.title-bar').exists()).toBe(true);
    });

    it('should update isAvailableForOutsourcing when outsource switch is toggled', async () => {
        const wrapper = setWrapper(undefined, {
            organisation: {
                hasOutsourceToggle: true,
                isAvailableForOutsourcing: false,
            } as Organisation,
        });

        expect(wrapper.vm.$store.getters['userInfo/organisation'].isAvailableForOutsourcing).toEqual(false);

        const outsourceSwitch = wrapper.findComponent({ name: 'BForm' }).findComponent({ name: 'BFormCheckbox' });
        await outsourceSwitch.vm.$emit('change');
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.$store.getters['userInfo/organisation'].isAvailableForOutsourcing).toEqual(true);
    });
});
