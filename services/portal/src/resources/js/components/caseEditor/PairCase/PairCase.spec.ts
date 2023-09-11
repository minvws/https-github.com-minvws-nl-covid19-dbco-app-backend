import { shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';
import PairCase from './PairCase.vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';

import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
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

        return shallowMount(PairCase, {
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

describe('PairCase.vue', () => {
    it('should disable show-pairing-code-button if userCanEdit is false', () => {
        (userCanEdit as Mock).mockImplementation(() => false);

        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: 'draft' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );

        expect(wrapper.find("[data-testid='show-pairing-code-button']").attributes().disabled).toBe('true');
    });

    it('should not disable show-pairing-code-button if userCanEdit is true', () => {
        (userCanEdit as Mock).mockImplementation(() => true);
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: 'draft' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );

        expect(wrapper.find("[data-testid='show-pairing-code-button']").attributes().disabled).toBe(undefined);
    });

    it('should set copy of "show-pairing-code-button" to "Maak nieuwe code" if bcoStatus is different from "draft"', () => {
        (userCanEdit as Mock).mockImplementation(() => true);
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: 'new' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );

        expect(wrapper.find("[data-testid='show-pairing-code-button']").text()).toBe('Maak nieuwe code');
    });

    it('should set copy of "show-pairing-code-button" to "Toon koppelcode" if bcoStatus is draft', () => {
        (userCanEdit as Mock).mockImplementation(() => true);
        const wrapper = createComponent(
            {},
            {
                meta: { schemaVersion: 2, bcoStatus: 'draft' },
                fragments: {
                    general: {
                        isPairingAllowed: true,
                    },
                },
            }
        );

        expect(wrapper.find("[data-testid='show-pairing-code-button']").text()).toBe('Toon koppelcode');
    });
});
