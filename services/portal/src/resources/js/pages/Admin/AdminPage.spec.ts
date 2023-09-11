import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import AdminPage from './AdminPage.vue';
import Vuex from 'vuex';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { Route } from 'vue-router';
import type VueRouter from 'vue-router';
import { PermissionV1 } from '@dbco/enum';

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        userInfoState: Partial<UserInfoState> = {},
        $route: Partial<Route> = { params: {} },
        $router: Partial<VueRouter> = {}
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };
        return shallowMount(AdminPage, {
            localVue,
            mocks: {
                $route,
                $router,
            },
            stubs: {
                ['router-view']: true,
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
        });
    }
);

describe('AdminPage.vue', () => {
    it('should show the admin page', () => {
        const wrapper = createComponent();
        expect(wrapper.exists()).toBe(true);
    });

    it('should show warning if no admin module is enabled', () => {
        const wrapper = createComponent();
        expect(wrapper.text()).contains('Geen beheer module gevonden');
    });

    it('should show router view if module is enabled', () => {
        const wrapper = createComponent({ permissions: [PermissionV1.VALUE_adminPolicyAdviceModule] });
        const expectedComponent = wrapper.find('router-view-stub');
        expect(expectedComponent.exists()).toBe(true);
    });

    it('should push "/beheren/beleidsversies" to $router if module is enabled and $route.path is "/beheren"', () => {
        const push = vi.fn();

        createComponent(
            { permissions: [PermissionV1.VALUE_adminPolicyAdviceModule] },
            { params: {}, path: '/beheren' },
            { push }
        );

        expect(push).toBeCalledWith('/beheren/beleidsversies');
    });
});
