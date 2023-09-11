import type { Assignment, AssignmentOption } from '@dbco/portal-api/assignment';
import { AssignmentOptionType, AssignmentType } from '@dbco/portal-api/assignment';
import i18n from '@/i18n/index';
import { usePlanner } from '@/store/planner/plannerStore';
import { setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import type { BDropdown } from 'bootstrap-vue';
import BootstrapVue from 'bootstrap-vue';
import { PiniaVuePlugin } from 'pinia';
import type { VueConstructor } from 'vue';
import AssignmentDropdown from './AssignmentDropdown.vue';

const data = {
    loading: false,
    openedOption: null,
    searchString: null,
};

const mockedOptions = [
    {
        type: AssignmentOptionType.OPTION,
        label: 'Niet toegewezen',
        isSelected: true,
        isEnabled: false,
        assignment: {
            assignedUserUuid: undefined,
            staleSince: '',
        },
    },
    {
        type: AssignmentOptionType.OPTION,
        label: 'Wachtrij',
        isSelected: false,
        isEnabled: true,
        isQueue: true,
        assignmentType: AssignmentType.CASELIST,
        assignment: {
            assignedCaseListUuid: '5d7eb4a7-1b36-479b-b840-ad7dd9e6429c',
            staleSince: '',
        },
    },
    {
        type: AssignmentOptionType.MENU,
        label: 'Lijsten',
        options: [
            {
                type: AssignmentOptionType.OPTION,
                label: 'Geen lijst',
                isSelected: true,
                isEnabled: false,
                isQueue: undefined,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    assignedCaseListUuid: undefined,
                    staleSince: '',
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: 'Johan',
                isSelected: false,
                isEnabled: true,
                isQueue: false,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    caseListUuid: '9d4affef-f290-4118-b31b-9739dd9dbaf8',
                    staleSince: '',
                },
            },
        ],
        isEnabled: true,
    },
    {
        type: AssignmentOptionType.MENU,
        label: 'Uitbesteden',
        options: [
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo GGD2',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.ORGANISATION,
                assignment: {
                    assignedOrganisationUuid: '20000000-0000-0000-0000-000000000000',
                    staleSince: '',
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo LS1',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.ORGANISATION,
                assignment: {
                    assignedOrganisationUuid: '10000000-0000-0000-0000-000000000000',
                    staleSince: '',
                },
            },
        ],
    },
    {
        type: AssignmentOptionType.SEPARATOR,
    },
    {
        type: AssignmentOptionType.OPTION,
        label: 'Demo Gebruiker 5',
        isSelected: false,
        isEnabled: true,
        assignmentType: AssignmentType.USER,
        assignment: {
            assignedUserUuid: '00000000-0000-0000-0000-100000000005',
            staleSince: '',
        },
    },
    {
        type: AssignmentOptionType.OPTION,
        label: 'Demo GGD1 Gebruiker',
        isSelected: false,
        isEnabled: true,
        assignmentType: AssignmentType.USER,
        assignment: {
            assignedUserUuid: '00000000-0000-0000-0000-000000000001',
            staleSince: '',
        },
    },
];

const dropdownRef: Partial<BDropdown> = {
    hide: vi.fn(),
    updatePopper: vi.fn(),
};

const createComponent = setupTest(
    (localVue: VueConstructor, props: object = {}, data: object = {}, plannerStoreState: object = {}) => {
        const pinia = createTestingPinia({
            initialState: {
                planner: plannerStoreState,
            },
            stubActions: false,
        });

        const planner = usePlanner();
        planner.fetchAssignmentOptions = vi.fn(() => Promise.resolve());

        return shallowMount<AssignmentDropdown>(AssignmentDropdown, {
            localVue,
            data: () => data,
            i18n,
            propsData: {
                ...props,
            },
            pinia,
            stubs: {
                BDropdown: true,
            },
        });
    }
);

describe('AssignmentDropdown.vue', () => {
    const localVue = createLocalVue();

    localVue.use(BootstrapVue);
    localVue.use(PiniaVuePlugin);

    it('should show dropdown on initial render', () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data);

        expect(wrapper.find('bdropdown-stub').exists()).toBe(true);
    });

    it('should fetch assignment options when dropdown is opened', async () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });

        const planner = usePlanner();

        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
        await wrapper.vm.openDropdown();

        expect(planner.fetchAssignmentOptions).toHaveBeenCalledTimes(1);
    });

    it('should filter options with type user into separate array', () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });

        expect(wrapper.vm.userOptions).toStrictEqual([
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo Gebruiker 5',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.USER,
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-100000000005',
                    staleSince: '',
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo GGD1 Gebruiker',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.USER,
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-000000000001',
                    staleSince: '',
                },
            },
        ]);
    });

    it('should filter options with type user into separate array', () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });

        expect(wrapper.vm.userOptions).toStrictEqual([
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo Gebruiker 5',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.USER,
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-100000000005',
                    staleSince: '',
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo GGD1 Gebruiker',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.USER,
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-000000000001',
                    staleSince: '',
                },
            },
        ]);
    });

    it('should show opened options when triggered', () => {
        const wrapper = createComponent(
            { staleSince: '', uuids: ['1234', '5678'] },
            {
                openedOption: 2,
            },
            {
                assignment: {
                    conflicts: undefined,
                    options: mockedOptions,
                    queued: undefined,
                },
            }
        );

        expect(wrapper.vm.visibleOptions).toStrictEqual([
            {
                type: AssignmentOptionType.OPTION,
                label: 'Geen lijst',
                isSelected: true,
                isEnabled: false,
                isQueue: undefined,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    assignedCaseListUuid: undefined,
                    staleSince: '',
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: 'Johan',
                isSelected: false,
                isEnabled: true,
                isQueue: false,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    caseListUuid: '9d4affef-f290-4118-b31b-9739dd9dbaf8',
                    staleSince: '',
                },
            },
        ]);
    });

    it('should filter user options based on search input', () => {
        const wrapper = createComponent(
            { staleSince: '', uuids: ['1234', '5678'] },
            {
                searchString: 'GGD1',
            },
            {
                assignment: {
                    conflicts: undefined,
                    options: mockedOptions,
                    queued: undefined,
                },
            }
        );

        expect(wrapper.vm.visibleUserOptions).toStrictEqual([
            {
                type: AssignmentOptionType.OPTION,
                label: 'Demo GGD1 Gebruiker',
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.USER,
                assignment: {
                    assignedUserUuid: '00000000-0000-0000-0000-000000000001',
                    staleSince: '',
                },
            },
        ]);
    });

    it.each([
        ['assignedUserUuid' as keyof Assignment, mockedOptions[5] as AssignmentOption, 5],
        ['assignedCaseListUuid' as keyof Assignment, mockedOptions[1] as AssignmentOption, 1],
        ['assignedOrganisationUuid' as keyof Assignment, mockedOptions[3].options?.[0] as AssignmentOption, 3],
        ['caseListUuid' as keyof Assignment, mockedOptions[2].options?.[1] as AssignmentOption, 2],
    ])('%#: queue assignment with "%s" property in store', async (assignment, option, index) => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
        await wrapper.vm.selectOption(option, index);

        const params: Assignment = {
            cases: ['1234', '5678'],
            staleSince: '',
        };

        const currentAssignment = option?.assignment?.[assignment];
        let currentParam = params[assignment];
        if (currentAssignment) {
            if (currentAssignment !== undefined) {
                if (typeof currentParam === 'string') {
                    currentParam = currentAssignment as string;
                } else if (Array.isArray(currentParam)) {
                    currentParam = currentAssignment as Array<string>;
                }
            }
        }

        if (assignment === 'caseListUuid') assignment = 'assignedCaseListUuid';
        expect(wrapper.vm.$pinia.state.value.planner.assignment.queued).toStrictEqual({
            uuids: ['1234', '5678'],
            params: { ...params, ...{ [assignment as keyof Assignment]: currentAssignment } },
        });
    });

    it('should set openOption to option index if option with type menu is selected', async () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });
        await wrapper.vm.selectOption(mockedOptions[2], 2);

        expect(wrapper.vm.openedOption).toBe(2);
    });

    it('should emit assignErrors when conflicts are detected', async () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });

        wrapper.vm.$pinia.state.value.planner.assignment = {
            ...wrapper.vm.$pinia.state.value.planner.assignment,
            conflicts: ['error1', 'error2'],
        };
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('assignErrors')).toBeTruthy();
    });

    it('should clear assignments in Store on when destroyed', async () => {
        const wrapper = createComponent({ staleSince: '', uuids: ['1234', '5678'] }, data, {
            assignment: {
                conflicts: undefined,
                options: mockedOptions,
                queued: undefined,
            },
        });

        await wrapper.destroy();

        expect(wrapper.vm.$pinia.state.value.planner.assignment.options).toStrictEqual([]);
    });
});
