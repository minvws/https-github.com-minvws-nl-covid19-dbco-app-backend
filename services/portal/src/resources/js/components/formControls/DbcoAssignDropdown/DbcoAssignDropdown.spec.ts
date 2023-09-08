import { caseApi } from '@dbco/portal-api';
import type { Assignment, AssignmentOption } from '@dbco/portal-api/assignment';
import { AssignmentOptionType, AssignmentType } from '@dbco/portal-api/assignment';
import i18n from '@/i18n';
import { fakerjs, setupTest } from '@/utils/test';
import fakeAssignmentOptions from '@/utils/__fakes__/fakeAssignmentOptions';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import { createTestingPinia } from '@pinia/testing';
import { shallowMount } from '@vue/test-utils';
import type { AxiosResponse } from 'axios';
import type { BDropdown } from 'bootstrap-vue';
import type { VueConstructor } from 'vue';
import DbcoAssignDropdown from './DbcoAssignDropdown.vue';

const defaultProps = {
    staleSince: fakerjs.date.past().toString(),
    uuid: fakerjs.custom.arrayOfUuids(),
};

const dropdownRef: Partial<BDropdown> = {
    hide: vi.fn(),
    updatePopper: vi.fn(),
};

const spyGetCall = vi.spyOn(caseApi, 'getAssignmentOptions').mockImplementation(() => Promise.resolve({}));
const mockUpdateCall = (expectedResponse: Promise<AxiosResponse>) =>
    vi.spyOn(caseApi, 'updateAssignment').mockImplementationOnce(() => expectedResponse);

const stubs = {
    BDropdown: true,
    BDropdownDivider: true,
    BDropdownItem: true,
    BFormInput: true,
    BInputGroup: true,
    BInputGroupAppend: true,
    BSpinner: true,
    BTooltip: true,
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        givenData: object = {},
        givenProps: object = defaultProps,
        givenPlannerStoreState: object = {}
    ) => {
        return shallowMount<DbcoAssignDropdown>(DbcoAssignDropdown, {
            data: () => givenData,
            localVue,
            propsData: givenProps,
            stubs,
            i18n,
            pinia: createTestingPinia({
                initialState: {
                    planner: givenPlannerStoreState,
                },
                stubActions: false,
            }),
        });
    }
);

describe('DbcoAssignDropdown.vue', () => {
    it('should fetch assignment options when dropdown is opened', async () => {
        // GIVEN the component renders
        const wrapper = createComponent();

        // WHEN the dropdown is opened
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
        await (wrapper.vm as any).openDropdown();

        // THEN the assignment options should be fetched with the given uuids
        expect(spyGetCall).toHaveBeenCalledWith(defaultProps.uuid);
    });

    it('should filter options with type user into separate array', () => {
        // GIVEN a set of options
        // WHEN the component renders
        const wrapper = createComponent({ options: fakeAssignmentOptions });

        // THEN the options with type user are filtered into a separate userOptions array
        expect((wrapper.vm as any).userOptions).toStrictEqual([fakeAssignmentOptions[5], fakeAssignmentOptions[6]]);
    });

    it('should show opened options when triggered', async () => {
        // GIVEN the component renders with a set of options
        const wrapper = createComponent({ options: fakeAssignmentOptions });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;

        // WHEN an option is selected
        const items = wrapper.findAll('.dropdown-option');
        await items.at(2).trigger('click');

        // THEN the opened options should be rendered
        const renderedOptions = wrapper.findAll('.dropdown-option');
        expect(renderedOptions.at(1).text()).toBe(fakeAssignmentOptions[2].options?.[0].label);
        expect(renderedOptions.at(2).text()).toBe(fakeAssignmentOptions[2].options?.[1].label);
    });

    it('should filter user options based on search input', () => {
        // GIVEN a searchable option
        const searchableFakeOption = {
            type: AssignmentOptionType.OPTION,
            label: 'Demo GGD1 Gebruiker',
            isSelected: false,
            isEnabled: true,
            assignmentType: AssignmentType.USER,
            assignment: {
                assignedUserUuid: fakerjs.string.uuid(),
                staleSince: '',
            },
        };

        // WHEN the component renders with a given searchString
        const wrapper = createComponent({
            searchString: 'GGD1',
            options: [...fakeAssignmentOptions, searchableFakeOption],
        });

        // THEN the option that matches the search should be visible
        expect((wrapper.vm as any).visibleUserOptions).toStrictEqual([searchableFakeOption]);
    });

    // assignment | option | index
    it.each<[keyof Assignment, AssignmentOption | undefined, number]>([
        ['assignedUserUuid', fakeAssignmentOptions[5], 5],
        ['assignedCaseListUuid', fakeAssignmentOptions[1], 1],
        ['assignedOrganisationUuid', fakeAssignmentOptions[3].options?.[0], 3],
        ['caseListUuid', fakeAssignmentOptions[2].options?.[1], 2],
    ])(
        '%#: make update api call with correct format for the given $assignment property',
        async (assignment, option, index) => {
            const spyUpdateCall = mockUpdateCall(Promise.resolve({} as AxiosResponse));
            const wrapper = createComponent({ options: fakeAssignmentOptions });
            wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
            await (wrapper.vm as any).selectOption(option, index);

            const params: Assignment = {
                cases: defaultProps.uuid.length > 1 ? defaultProps.uuid : undefined,
                staleSince: defaultProps.staleSince,
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
            expect(spyUpdateCall).toHaveBeenCalledWith(defaultProps.uuid, {
                ...params,
                ...{ [assignment as keyof Assignment]: currentAssignment },
            });
        }
    );

    it('should hide the dropdown after a successful update call', async () => {
        // GIVEN an update call resolves with status 200
        mockUpdateCall(Promise.resolve({ status: 200 } as AxiosResponse));

        const wrapper = createComponent({ options: fakeAssignmentOptions });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;

        // WHEN that call is finished
        await (wrapper.vm as any).selectOption(fakeAssignmentOptions[5], 5);
        await wrapper.vm.$nextTick();

        // THEN the dropdown should be hidden
        expect(dropdownRef.hide).toBeCalledTimes(1);
    });

    it('should update the selected case after a successful update call when there is only 1 case', async () => {
        // GIVEN an update call resolves with status 200 and a case
        const caseFromApi = fakePlannerCaseListItem;
        mockUpdateCall(Promise.resolve({ data: caseFromApi, status: 200 } as AxiosResponse));

        // AND the component rendered with a single case
        const wrapper = createComponent(
            { options: fakeAssignmentOptions },
            { staleSince: fakerjs.date.past().toString(), uuid: [fakerjs.string.uuid()] }
        );
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;

        // WHEN the update call is finished
        await (wrapper.vm as any).selectOption(fakeAssignmentOptions[5], 5);
        await wrapper.vm.$nextTick();

        // THEN the case from the API should be the selected case
        expect(wrapper.vm.$pinia.state.value.planner.selectedCase).toBe(caseFromApi);
    });

    it.skip('should show an assignment conflict modal when an update call resolves with a case for multiple case updates', async () => {
        // GIVEN an update call resolves with status 200 and a case
        const caseFromApi = fakePlannerCaseListItem;
        mockUpdateCall(Promise.resolve({ data: caseFromApi, status: 200 } as AxiosResponse));

        // AND the component rendered with multiple cases (defaultProps)
        const wrapper = createComponent({ options: fakeAssignmentOptions });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;

        // WHEN the update call is finished
        await (wrapper.vm as any).selectOption(fakeAssignmentOptions[5], 5);

        // THEN the assignment conflict modal should be shown
        expect(wrapper.emitted('assignErrors')).toBeTruthy();
    });

    it.skip('should show an error message tooltip when the update call rejects with status code 422', async () => {
        // GIVEN an update call rejects with status code 422
        mockUpdateCall(Promise.reject({ response: { status: 422 } }));

        // AND the component renders
        const wrapper = createComponent({ options: fakeAssignmentOptions });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;

        // WHEN the update call is finished
        await (wrapper.vm as any).selectOption(fakeAssignmentOptions[5], 5);

        // THEN the tooltip should be shown
        expect(wrapper.emitted('bv::show::tooltip')).toBeTruthy();
    });

    it.skip('should show an assignment conflict modal when the update call rejects with status code 409', async () => {
        // GIVEN an update call rejects with status code 409
        mockUpdateCall(Promise.reject({ response: { data: fakePlannerCaseListItem, status: 409 } }));

        // AND the component renders
        const wrapper = createComponent({ options: fakeAssignmentOptions });
        wrapper.vm.$refs.dropdown = dropdownRef as BDropdown;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        wrapper.vm.$t = vi.fn() as any;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        wrapper.vm.$tc = vi.fn() as any;

        // WHEN the update call is finished
        await (wrapper.vm as AnyObject).selectOption(fakeAssignmentOptions[5], 5);

        // THEN the assignment conflict modal should be shown
        expect(wrapper.emitted('assignErrors')).toBeTruthy();
    });
});
