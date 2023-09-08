import { BcoPhaseV1, PermissionV1 } from '@dbco/enum';
import type { Organisation } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { User } from '@dbco/portal-api/user';
import { Role } from '@dbco/portal-api/user';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';
// @ts-ignore
import DbcoUserInfo from './DbcoUserInfo.vue';

describe('DbcoUserInfo.vue', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getWrapper = (
        {
            user = {} as Partial<User>,
            organisation = {} as Partial<Organisation>,
            permissions = [] as PermissionV1[],
        } = {},
        state: object = {}
    ) => {
        const store = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...state,
            },
        };

        return shallowMount(DbcoUserInfo, {
            localVue,
            propsData: {
                user: {
                    name: 'testUser',
                    roles: [Role.user],
                    uuid: '1234',
                    ...user,
                },
                organisation: {
                    abbreviation: 'abbr',
                    bcoPhase: null,
                    type: 'regional',
                    hasOutsourceToggle: false,
                    isAvailableForOutsourcing: false,
                    name: 'testOrganisation',
                    uuid: '5678',
                    ...organisation,
                },
                permissions: [...permissions],
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: store,
                },
            }),
        });
    };

    it('should load', () => {
        const wrapper = getWrapper();

        expect(wrapper.findComponent({ name: 'DbcoUserInfo' }).exists()).toBe(true);
    });

    it('should persist the userInfo in the store', () => {
        const user: User = {
            name: 'userInfoName',
            roles: [Role.user, Role.compliance],
            uuid: '1212',
        };
        const organisation: Organisation = {
            abbreviation: 'org',
            bcoPhase: BcoPhaseV1.VALUE_2,
            type: 'regional',
            hasOutsourceToggle: true,
            isAvailableForOutsourcing: true,
            name: 'the name',
            uuid: '7878',
        };
        const permissions = [PermissionV1.VALUE_caseBsnLookup, PermissionV1.VALUE_contextEdit];

        const wrapper = getWrapper({ user, organisation, permissions });

        expect(wrapper.vm.$store.state.userInfo).toEqual({ loaded: true, user, organisation, permissions });
    });
});
