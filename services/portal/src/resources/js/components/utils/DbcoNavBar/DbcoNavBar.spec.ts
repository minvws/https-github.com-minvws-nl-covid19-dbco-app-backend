import { BNavItem, BNavItemDropdown, BNavbar, BNavbarNav } from 'bootstrap-vue';
import { BcoPhaseV1, bcoPhaseV1Options, PermissionV1 } from '@dbco/enum';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';

import DbcoNavBar from './DbcoNavBar.vue';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import env from '@/env';
import { fakeOrganisation } from '@/utils/__fakes__/organisation';
import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';

vi.mock('@/env');

const menuItemWidth = 100;
Object.defineProperty(HTMLElement.prototype, 'clientWidth', { value: menuItemWidth });

const createComponent = setupTest(
    async (
        localVue: VueConstructor,
        props: object = {},
        userInfoState: Partial<UserInfoState> = {},
        navigationWidth?: number
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        const DbcoMultilevelDropdownMock = {
            template: '<ul><slot name="profile"></slot><slot></slot></li></ul>',
        };

        const wrapper = shallowMount(DbcoNavBar, {
            localVue,
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
            attachTo: document.body,
            stubs: {
                BNavbar,
                BNavbarNav,
                BNavItem,
                BNavItemDropdown,
                DbcoMultilevelDropdown: DbcoMultilevelDropdownMock,
            },
        });

        if (navigationWidth) {
            Object.defineProperty(wrapper.vm.$refs.navigation, 'clientWidth', {
                configurable: true,
                value: navigationWidth,
            });
        }

        await wrapper.vm.$nextTick();

        return wrapper;
    }
);

describe('DbcoNavBar.vue', () => {
    it('should show all items and hide dropdown if navigation is wide enough', async () => {
        const wrapper = await createComponent(
            { section: '' },
            { permissions: Object.values(PermissionV1) },
            Number.MAX_SAFE_INTEGER
        );

        expect(wrapper.findAll('[data-testid="navigation"] > .nav-item:not([data-testid="dropdown"])').length).toBe(
            wrapper.vm.menuItems.length
        );
        expect(wrapper.findByTestId('dropdown').isVisible()).toBe(false);
    });

    it('should put all but the active items in the dropdown if NOT wide enough', async () => {
        const wrapper = await createComponent(
            { section: '' },
            { permissions: Object.values(PermissionV1) },
            menuItemWidth
        );

        expect(wrapper.findAll('[data-testid="navigation"] > .nav-item:not([data-testid="dropdown"])').length).toBe(1);
        expect(wrapper.findAll('[data-testid="dropdown"] .nav-item').length).toBe(wrapper.vm.menuItems.length - 1);
        expect(wrapper.findByTestId('dropdown').isVisible()).toBe(true);
    });

    it('should move items on resize', async () => {
        const wrapper = await createComponent(
            { section: '' },
            { permissions: Object.values(PermissionV1) },
            Number.MAX_SAFE_INTEGER
        );

        // Resize to small width
        Object.defineProperty(wrapper.vm.$refs.navigation, 'clientWidth', { value: menuItemWidth });
        dispatchEvent(new Event('resize'));

        expect(wrapper.findAll('[data-testid="navigation"] > .nav-item:not([data-testid="dropdown"])').length).toBe(1);
        expect(wrapper.findAll('[data-testid="dropdown"] .nav-item').length).toBe(wrapper.vm.menuItems.length - 1);
        expect(wrapper.findByTestId('dropdown').isVisible()).toBe(true);
    });

    it(`should show DbcoPhaseBadge if permission includes "${PermissionV1.VALUE_organisationUpdate}" and organisation is set`, async () => {
        const wrapper = await createComponent(
            { section: '' },
            { organisation: fakeOrganisation, permissions: [PermissionV1.VALUE_organisationUpdate] }
        );

        expect(wrapper.findComponent({ name: 'DbcoPhaseBadge' }).exists()).toBe(true);
    });

    it(`should NOT show DbcoPhaseBadge if permission includes "${PermissionV1.VALUE_organisationUpdate}" and organisation is NOT set`, async () => {
        const wrapper = await createComponent(
            { section: '' },
            { permissions: [PermissionV1.VALUE_organisationUpdate] }
        );

        expect(wrapper.findComponent({ name: 'DbcoPhaseBadge' }).exists()).toBe(false);
    });

    it(`should NOT show DbcoPhaseBadge if permission DOESN'T include "${PermissionV1.VALUE_organisationUpdate}" and organisation is set`, async () => {
        const wrapper = await createComponent({ section: '' }, { organisation: fakeOrganisation });

        expect(wrapper.findComponent({ name: 'DbcoPhaseBadge' }).exists()).toBe(false);
    });

    it('should show BCO Phase label if organisation.bcoPhase is set', async () => {
        const wrapper = await createComponent(
            { section: '' },
            {
                organisation: {
                    ...fakeOrganisation,
                    bcoPhase: BcoPhaseV1.VALUE_2,
                },
                permissions: [PermissionV1.VALUE_organisationUpdate],
            }
        );

        expect(wrapper.findComponent({ name: 'DbcoMultilevelDropdown' }).vm.$attrs.options).toEqual(
            expect.arrayContaining([
                expect.objectContaining({
                    label: bcoPhaseV1Options[BcoPhaseV1.VALUE_2],
                }),
            ])
        );
    });

    it(`should show "${bcoPhaseV1Options.none}" if organisation.bcoPhase is NOT set`, async () => {
        const wrapper = await createComponent(
            { section: '' },
            {
                organisation: {
                    ...fakeOrganisation,
                    bcoPhase: null,
                },
                permissions: [PermissionV1.VALUE_organisationUpdate],
            }
        );

        expect(wrapper.findComponent({ name: 'DbcoMultilevelDropdown' }).vm.$attrs.options).toEqual(
            expect.arrayContaining([
                expect.objectContaining({
                    label: bcoPhaseV1Options.none,
                }),
            ])
        );
    });

    it.each([
        {
            permission: PermissionV1.VALUE_caseListUserCases,
            label: 'Mijn cases',
            sections: ['editcase', 'cases', ''],
        },
        {
            permission: PermissionV1.VALUE_caseViewCallToAction,
            label: 'Taken',
            sections: ['taken'],
        },
        {
            permission: PermissionV1.VALUE_caseListPlannerCases,
            label: 'Werkverdeling',
            sections: ['planner'],
        },
        {
            permission: PermissionV1.VALUE_callcenterView,
            label: 'Dossier zoeken',
            sections: ['callcenter'],
        },
        {
            permission: PermissionV1.VALUE_expertQuestionConversationCoach,
            label: 'Gesprekscoach',
            sections: ['gesprekscoach'],
        },
        {
            permission: PermissionV1.VALUE_expertQuestionMedicalSupervisor,
            label: 'Medische supervisie',
            sections: ['medische-supervisie'],
        },
        {
            permission: PermissionV1.VALUE_placeList,
            label: 'Contexten',
            sections: ['places'],
        },
        {
            permission: PermissionV1.VALUE_caseListAccessRequests,
            label: 'Compliance',
            sections: ['compliance'],
        },
    ])(
        'should show "$label" if user has permission $permission and highlight on sections $sections',
        async ({ permission, label, sections }) => {
            const wrapper = await createComponent({ section: '' }, { permissions: [permission] });

            const button = wrapper.findComponent({ name: 'BNavItem' });
            expect(button.exists()).toBe(true);
            expect(button.text()).toEqual(label);

            for (const section of sections) {
                await wrapper.setProps({ section });

                expect(button.find('.nav-link').classes('active')).toBe(true);
            }
        }
    );

    it(`should not show "Beheren" if permission includes "${PermissionV1.VALUE_adminView}" and env.isAdminViewEnabled is false`, async () => {
        env.isAdminViewEnabled = false;
        const wrapper = await createComponent({ section: '' }, { permissions: [PermissionV1.VALUE_adminView] });

        const button = wrapper.findComponent({ name: 'BNavItem' });
        expect(button.exists()).toBe(false);

        env.isAdminViewEnabled = true;
    });
});
