import Vuex from 'vuex';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import CovidCaseHeaderBar from './CovidCaseHeaderBar.vue';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { PermissionV1 } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { useEventBus } from '@/composables/useEventBus';
vi.mock('@/utils/interfaceState');

const defaultProp = {
    covidCase: {
        uuid: '9f5fd584-6294-4bb1-a90f-74075a79d04e',
        meta: {
            caseId: 'bla',
            organisation: null,
        },
        fragments: {
            index: {
                firstname: 'John',
                lastname: 'Doe',
            },
        },
    },
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props: object = {},
        userInfoState: Partial<UserInfoState> = {},
        indexStoreState: Partial<IndexStoreState> = {}
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        return shallowMount(CovidCaseHeaderBar, {
            localVue,
            stubs: {
                LastUpdated: true,
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                    index: indexStoreModule,
                },
            }),
            propsData: {
                ...defaultProp,
                ...props,
            },
        });
    }
);

describe('CovidCaseHeaderBar.vue', () => {
    it('should load', () => {
        // ARRANGE
        const wrapper = createComponent();

        // ASSERT
        expect(wrapper.find('div').exists()).toBe(true);
    });

    it('should show case name if uuid exists', async () => {
        const prop = { ...defaultProp };
        const wrapper = createComponent(prop);
        await flushCallStack();
        expect(wrapper.find(`[data-testid=text-covidCase]`).exists()).toBe(true);
    });

    it('should not show case name if uuid does not exists', async () => {
        const prop = {
            ...defaultProp,
            ...{
                covidCase: {
                    uuid: null,
                },
            },
        };
        const wrapper = createComponent(prop);
        await flushCallStack();
        expect(wrapper.find(`[data-testid=text-covidCase]`).exists()).toBe(false);
    });

    it('when user has "caseEditContactStatus" permission the edit case status dropdown must be shown', async () => {
        const userInfoState: Partial<UserInfoState> = { permissions: [PermissionV1.VALUE_caseEditContactStatus] };

        const wrapper = await createComponent({}, userInfoState);

        // Test somehow needs double tick
        await flushCallStack();
        expect(wrapper.find(`[data-testid=edit-case-status-dropdown]`).exists()).toBe(true);
    });

    it('when user doesnt have "caseEditContactStatus" permission the edit case status dropdown must not be shown', async () => {
        const userInfoState: Partial<UserInfoState> = { permissions: [] };

        const wrapper = await createComponent({}, userInfoState);

        // Test somehow needs double tick
        await flushCallStack();

        expect(wrapper.find(`[data-testid=edit-case-status-dropdown]`).exists()).toBe(false);
    });

    it('when click edit close case drowndown item event should be emitted', async () => {
        const userInfoState: Partial<UserInfoState> = { permissions: [PermissionV1.VALUE_caseEditContactStatus] };

        const eventBus = useEventBus();
        vi.spyOn(eventBus, '$emit');

        const wrapper = await createComponent({}, userInfoState);

        // Test somehow needs double tick
        await flushCallStack();

        const dropdown = wrapper.find(`[data-testid=edit-case-status-dropdown]`);
        const dropdownItems = dropdown.findAllComponents({ name: 'BDropdownItem' });

        await dropdownItems.at(0).vm.$emit('click');
        expect(eventBus.$emit).toHaveBeenCalledWith('open-osiris-modal');
    });

    it.each([
        [false, false],
        [true, true],
    ])('when userCanEdit is "%s" button visibility should be "%s" ', async (isViewOnly, expected) => {
        (userCanEdit as Mock).mockImplementation(() => isViewOnly);

        const wrapper = await createComponent({});

        // Test somehow needs double tick
        await flushCallStack();

        expect(wrapper.find(`[data-testid=supervision-question-button]`).exists()).toBe(expected);
    });

    it.each([
        [false, false],
        [true, true],
    ])('when permissions are "%s" button visibility should be "%s" ', async (isViewOnly, expected) => {
        (userCanEdit as Mock).mockImplementation(() => isViewOnly);

        const wrapper = createComponent({});

        await flushCallStack();

        expect(wrapper.find(`[data-testid=create-call-to-action-button]`).exists()).toBe(expected);
    });

    it('should show phaseDropdown', async () => {
        const prop = { ...defaultProp };
        const wrapper = createComponent(prop);
        await flushCallStack();
        expect(wrapper.find(`[data-testid=dropdown-phase]`).exists()).toBe(true);
    });

    it.each([
        [true, true, false], // organisation is set and current is true so title and badge should not show
        [true, false, true], // organisation is set and current is false so title and badge should show
        [false, false, false], // organisation is not set and current is not set so title and badge should not show
    ])(
        'if organisation exists is "%s" and if isCurrent is "%s" then abbriviation and badge should be "%s"',
        async (organisation, isCurrent, expected) => {
            const prop = { ...defaultProp };

            prop.covidCase.meta.organisation = null;
            if (organisation) {
                // @ts-expect-error
                prop.covidCase.meta.organisation = {
                    uuid: '00000000-0000-0000-0000-000000000000',
                    abbreviation: 'GGD1',
                    isCurrent: isCurrent,
                };
            }

            const wrapper = await createComponent(prop);
            await flushCallStack();

            if (expected === true) {
                expect(wrapper.find(`[data-testid=text-organisation]`).text()).toContain('GGD1-bla');
            } else {
                expect(wrapper.find(`[data-testid=text-organisation]`).text()).toContain('bla');
            }

            expect(wrapper.find(`[data-testid=badge-organisation]`).exists()).toBe(expected);
        }
    );
});
