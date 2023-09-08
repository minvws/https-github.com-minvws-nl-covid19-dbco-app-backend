import { shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';
import ReversePairCase from './ReversePairCase.vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';

import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { PermissionV1 } from '@dbco/enum';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { userCanEdit } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
vi.mock('@/utils/interfaceState');

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        data: object = {},
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoState: Partial<UserInfoState> = {},
        propsData: object = {}
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

        return shallowMount(ReversePairCase, {
            localVue,
            data: () => data,
            propsData,
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
        });
    }
);

describe('ReversePairCase.vue', () => {
    it('if userCanEdit is true, dont disable "show-pairing-checkbox"', () => {
        (userCanEdit as Mock).mockImplementation(() => true);
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: '' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );
        expect(wrapper.find("[data-testid='show-pairing-checkbox']").attributes().disabled).toBe(undefined);
    });

    it('if userCanEdit is false, disable "show-pairing-checkbox"', () => {
        (userCanEdit as Mock).mockImplementation(() => false);
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: '' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );

        expect(wrapper.find("[data-testid='show-pairing-checkbox']").attributes().disabled).toBe('true');
    });

    it('should disable "show-pairing-checkbox" if isPairingActive is true', () => {
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: '', indexStatus: 'fake_status' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            },
            // We want to give the user permission so we know for sure the isPairingActive prop is doing the job
            { permissions: [PermissionV1.VALUE_caseUserEdit] }
        );

        expect(wrapper.vm.isPairingActive).toBe(true);
        expect(wrapper.find("[data-testid='show-pairing-checkbox']").attributes().disabled).toBe('true');
    });
});
