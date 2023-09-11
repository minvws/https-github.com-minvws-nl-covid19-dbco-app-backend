import BootstrapVue from 'bootstrap-vue';
import Vuex from 'vuex';

import type { UntypedWrapper } from '@/utils/test';
import { createContainer, flushCallStack } from '@/utils/test';
import { createLocalVue, shallowMount } from '@vue/test-utils';

import CovidCaseEdit from './CovidCaseEdit.vue';
import TabBar from '../TabBar/TabBar.vue';

import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import i18n from '@/i18n/index';
import { createTestingPinia } from '@pinia/testing';
import { PiniaVuePlugin } from 'pinia';

import mockOfRootSchemaStub from '@/components/form/ts/__stubs__/rootSchema';
import { hasCaseLock } from '@/utils/interfaceState';

import env from '@/env';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { caseApi } from '@dbco/portal-api';
import { fakeCaseLockResponseLocked } from '@/utils/__fakes__/caselock';
import TypingHelpers from '@/plugins/typings';
import { PermissionV1 } from '@dbco/enum';

vi.mock('@/utils/interfaceState');

vi.mock('@/components/form/ts/formSchema', () => ({
    getRootSchema: vi.fn(() => mockOfRootSchemaStub),
}));

const url = 'http://dummy.com';
Object.defineProperty(window, 'location', {
    value: {
        href: url,
        reload: vi.fn(),
    },
});
Object.defineProperty(window, 'history', {
    value: {
        replaceState: vi.fn(),
    },
});
Object.defineProperty(window, 'removeEventListener', {
    value: vi.fn(),
});
Object.defineProperty(window, 'scrollTo', {
    value: vi.fn(),
});

describe('CovidCaseEdit.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(PiniaVuePlugin);
    localVue.use(TypingHelpers);

    const getWrapper = (
        props?: object,
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoState: Partial<UserInfoState> = {}
    ) => {
        const container = createContainer();
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

        return shallowMount(CovidCaseEdit, {
            stubs: {
                LastUpdated: true,
                FormInfo: true,
                FormRenderer: true,
                CaseUpdateInfoBar: true,
                TabBar,
            },
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            localVue,
            pinia: createTestingPinia({
                initialState: {
                    caseLock: {
                        user: {
                            name: '',
                            organisation: '',
                        },
                        removed: false,
                    },
                },
                stubActions: false,
            }),
            i18n,
            propsData: {
                caseUuid: 'db232671-cf13-4b77-a391-c361419d6d95',
                ...props,
            },
            attachTo: container,
        }) as UntypedWrapper;
    };

    beforeEach(() => {
        vi.spyOn(caseApi, 'getFragments').mockImplementationOnce(() =>
            Promise.resolve({
                data: {
                    general: {
                        schemaVersion: 1,
                        source: null,
                        reference: '1234567',
                        organisation: {
                            uuid: '00000000-0000-0000-0000-000000000000',
                            name: 'Demo GGD1',
                        },
                        createdAt: '2021-11-15T09:16:56Z',
                    },
                    index: {
                        firstname: 'Berend',
                        lastname: 'Groot',
                        bsnNotes: null,
                        dateOfBirth: '1990-03-03',
                        address: {
                            schemaVersion: 1,
                            postalCode: '3583Ep',
                            houseNumber: '50',
                            houseNumberSuffix: null,
                            street: 'Prins Hendriklaan',
                            town: 'Utrecht',
                        },
                        bsnCensored: '******008',
                        bsnLetters: 'ZZ',
                        hasNoBsnOrAddress: null,
                    },
                },
            })
        );

        vi.spyOn(caseApi, 'getMeta').mockImplementationOnce(() =>
            Promise.resolve({
                case: {
                    uuid: 'db232671-cf13-4b77-a391-c361419d6d95',
                    organisation: {
                        uuid: '00000000-0000-0000-0000-000000000000',
                        abbreviation: 'GGD1',
                        name: 'Demo GGD1',
                    },
                    userCanEdit: true,
                    isLocked: false,
                },
            })
        );
    });

    it('should load', async () => {
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.find('div').exists()).toBe(true);
        expect(wrapper.vm.loaded).toBe(true);
    });

    it('should call this.onScroll() on window.eventListener("scroll") ', async () => {
        const wrapper = getWrapper();

        await flushCallStack();
        window.dispatchEvent(new Event('scroll'));
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.sticky).toBe(true);
    });

    it('should change tab on load when hash isset', async () => {
        window.location.href = url + '#medical';
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.vm.activeTab).toBe(1);
    });

    it('should not change tab on load when hash is wrong', async () => {
        window.location.href = url + '#test';
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.vm.activeTab).toBe(0);
    });

    it('should scroll to top of tabs after a tab change', async () => {
        window.location.href = url + '#medical';
        const wrapper = getWrapper();
        await flushCallStack();

        // Await the timeout
        await new Promise((resolve) => setTimeout(resolve, 300));
        expect(window.scrollTo).toHaveBeenCalledWith({ behavior: 'smooth', top: wrapper.vm.$data.scrollToTopHeight });
    });

    it('should have supervisionmodal', async () => {
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.findComponent({ name: 'SupervisionModal' }).exists()).toBe(true);
    });

    it('should have CaseUpdateInfoBar when isIntakeMatchCaseEnabled = true', async () => {
        env.isIntakeMatchCaseEnabled = true;
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.findComponent({ name: 'CaseUpdateInfoBar' }).exists()).toBe(true);
    });

    it('should not have CaseUpdateInfoBar when isIntakeMatchCaseEnabled = false', async () => {
        env.isIntakeMatchCaseEnabled = false;
        const wrapper = getWrapper();
        await flushCallStack();

        expect(wrapper.findComponent({ name: 'CaseUpdateInfoBar' }).exists()).toBe(false);
    });

    it('should show banner when case is locked for user with edit permission', async () => {
        // GIVEN an initially locked case
        (hasCaseLock as Mock).mockImplementationOnce(() => true);

        // AND an active case lock when polling
        const caseLockResponse = fakeCaseLockResponseLocked;
        vi.spyOn(caseApi, 'getCaseLock').mockImplementationOnce(() => Promise.resolve(caseLockResponse));

        // WHEN the component renders for a user with edit permission
        const wrapper = getWrapper(undefined, {}, { permissions: [PermissionV1.VALUE_caseUserEdit] });
        await flushCallStack();

        // THEN a banner is shown
        expect(wrapper.find('forminfo-stub').exists()).toBe(true);
    });

    it('should NOT show banner when case is locked for user without edit permission', async () => {
        // GIVEN an initially locked case
        (hasCaseLock as Mock).mockImplementationOnce(() => true);

        // AND an active case lock when polling
        const caseLockResponse = fakeCaseLockResponseLocked;
        vi.spyOn(caseApi, 'getCaseLock').mockImplementationOnce(() => Promise.resolve(caseLockResponse));

        // WHEN the component renders for a user without edit permission
        const wrapper = getWrapper();
        await flushCallStack();

        // THEN a banner is shown
        expect(wrapper.find('forminfo-stub').exists()).toBe(false);
    });

    it('should show banner with translated message and action when case-lock is removed', async () => {
        // GIVEN an initially locked case
        (hasCaseLock as Mock).mockImplementationOnce(() => true);

        // WHEN the component renders for a user with edit permission
        const wrapper = getWrapper(undefined, {}, { permissions: [PermissionV1.VALUE_caseUserEdit] });
        await flushCallStack();

        // AND the caseLock store states that the caseLock has been removed
        wrapper.vm.$pinia.state.value.caseLock.caseLock.removed = true;
        await flushCallStack();

        // THEN a banner is shown with a translated message and an action
        const expectedTranslation = i18n.t('components.caseUnlockNotification.title');
        const expectedActionText = i18n.t('components.caseUnlockNotification.action');
        const banner = wrapper.findComponent({ name: 'FormInfo' });
        expect(banner.attributes('text')).toBe(expectedTranslation);
        expect(banner.attributes('actiontext')).toBe(expectedActionText);
        expect(banner.attributes('hasaction')).toBe('true');
    });

    it('should reload case when banner action is triggered', async () => {
        // GIVEN a banner is shown with a translated message and an action (see previous test)
        (hasCaseLock as Mock).mockImplementationOnce(() => true);

        // AND the component renders for a user with edit permission
        const wrapper = getWrapper(undefined, {}, { permissions: [PermissionV1.VALUE_caseUserEdit] });
        await flushCallStack();

        wrapper.vm.$pinia.state.value.caseLock.caseLock.removed = true;
        await flushCallStack();

        const banner = wrapper.findComponent({ name: 'FormInfo' });

        // WHEN the banner action is triggered
        await banner.vm.$emit('actionTriggered');
        await flushCallStack();

        // THEN the case is reloaded
        expect(window.location.reload).toHaveBeenCalledTimes(1);
    });

    it('should remove scroll listener and stop store polling when leaving the case', async () => {
        // GIVEN the component renders
        const wrapper = getWrapper();
        await flushCallStack();

        // WHEN it is destroyed
        await wrapper.destroy();

        // THEN the case is reloaded
        expect(window.removeEventListener).toHaveBeenCalledTimes(1);
    });
});
