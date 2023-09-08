import Vuex from 'vuex';

import placeStore from '@/store/place/placeStore';
import organisationStore from '@/store/organisation/organisationStore';

import { shallowMount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';

import OrganisationEdit from './OrganisationEdit.vue';
import type { PlaceStoreState } from '@/store/place/placeTypes';
import type { OrganisationStoreState } from '@/store/organisation/organisationTypes';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { VueConstructor } from 'vue';

const mockOrganisations = [
    {
        uuid: '00000000-0000-0000-0000-000000000000',
        name: 'Demo GGD1',
    },
    {
        uuid: '0296ab48-1576-4262-af38-78e9ef06ed07',
        name: 'GGD Gelderland-Midden',
    },
    {
        uuid: '0535e4cd-af98-4113-999e-888f9fdf2a40',
        name: 'GGD Noord- en Oost Gelderland',
    },
    {
        uuid: '08eee942-53ef-4386-96b1-e70bd80d464b',
        name: 'GGD Hollands Noorden',
    },
];

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        placeStoreState?: PlaceStoreState,
        organisationStoreState?: OrganisationStoreState,
        userInfoStoreState?: UserInfoState
    ) => {
        const placeStoreModule = {
            ...placeStore,
            state: {
                ...placeStore.state,
                ...placeStoreState,
            },
        };

        const organisationStoreModule = {
            ...organisationStore,
            state: {
                ...organisationStore.state,
                ...organisationStoreState,
            },
        };
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoStoreState,
            },
        };

        return shallowMount<OrganisationEdit>(OrganisationEdit, {
            localVue,
            store: new Vuex.Store({
                modules: {
                    place: placeStoreModule,
                    organisation: organisationStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: {
                BDropdown: true,
                BDropdownItem: true,
                BFormCheckbox: true,
            },
        });
    }
);

describe('OrganisationEdit.vue', () => {
    it('should NOT render overwrite-dropdown when overwrite is false', () => {
        const wrapper = createComponent();
        const dropdowns = wrapper.findAllComponents({ name: 'BDropdown' });
        expect(dropdowns.length).toBe(0);
    });

    it('should NOT render overwrite checkbox when there is no organisation to overwrite', () => {
        const wrapper = createComponent();
        const checkbox = wrapper.findAllComponents({ name: 'BFormCheckbox' });
        expect(checkbox.length).toBe(0);
    });

    it('should render overwrite-dropdown when manual overwrite is triggered', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuidByPostalCode: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        await wrapper.vm.getPlaceOrganisationByPostalCode();
        const checkbox = wrapper.findComponent({ name: 'BFormCheckbox' });
        await checkbox.vm.$emit('change');
        const dropdowns = wrapper.findAllComponents({ name: 'BDropdown' });

        expect(wrapper.vm.overwrite).toBe(true);
        expect(dropdowns.length).toBe(1);
    });

    it('should render overwrite-dropdown with current organisation if available', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: mockOrganisations[0],
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        await wrapper.vm.getPlaceOrganisation();
        const dropdownText = wrapper.findComponent({ name: 'BDropdown' }).props('text');

        expect(dropdownText).toBe(mockOrganisations[0].name);
    });

    it('should render overwrite warning when organisation other than that of the user is selected', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[0].uuid,
                    organisationUuidByPostalCode: mockOrganisations[1].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            },
            {
                loaded: true,
                organisation: {
                    abbreviation: '',
                    bcoPhase: null,
                    hasOutsourceToggle: false,
                    isAvailableForOutsourcing: false,
                    name: '',
                    type: '',
                    uuid: mockOrganisations[1].uuid,
                },
            }
        );
        await wrapper.vm.getPlaceOrganisationByPostalCode();
        wrapper.vm.overwrite = true;

        expect(wrapper.html()).not.toContain('Dit is niet je eigen GGD-regio.');

        expect(wrapper.vm.overwrite).toBe(true);
        const dropdownitems = wrapper.findAllComponents({ name: 'BDropdownItem' });
        const dropdownitem = dropdownitems.at(2);
        await dropdownitem.vm.$emit('click');

        expect(wrapper.vm.showOverwriteWarning).toBe(true);
        expect(wrapper.html()).toContain('Dit is niet je eigen GGD-regio.');
    });

    it('should commit organisation to store when organisation selected', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuidByPostalCode: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        await wrapper.vm.getPlaceOrganisationByPostalCode();
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');
        const checkbox = wrapper.findComponent({ name: 'BFormCheckbox' });
        await checkbox.vm.$emit('change');
        const dropdownitems = wrapper.findAllComponents({ name: 'BDropdownItem' });
        const dropdownitem = dropdownitems.at(1);
        await dropdownitem.vm.$emit('click');

        expect(spyOnCommit).toHaveBeenCalledWith('organisation/SET_CURRENT', mockOrganisations[1]);
    });

    it('should unset organisation in store when overwrite is unchecked', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[1].uuid,
                    organisationUuidByPostalCode: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        await wrapper.vm.getPlaceOrganisation();
        await wrapper.vm.getPlaceOrganisationByPostalCode();
        wrapper.vm.overwrite = true;
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');
        const checkbox = wrapper.findComponent({ name: 'BFormCheckbox' });
        await checkbox.vm.$emit('change');

        expect(spyOnCommit).toHaveBeenCalledWith('organisation/SET_CURRENT', undefined);
    });

    it('should change the region based on postal code when an address update changes it', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[1].uuid,
                    organisationUuidByPostalCode: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: mockOrganisations[3].uuid,
                error: '',
            }
        );

        // Wait for store to process update
        await flushCallStack();

        const input = wrapper.findComponent({ name: 'BFormInput' });

        expect(input.props('value')).toBe(mockOrganisations[3].name);
    });

    it('should commit organisation/CLEAR_KEEP_ALL when destroyed', () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[1].uuid,
                    organisationUuidByPostalCode: mockOrganisations[0].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: undefined,
                currentFromAddressSearch: mockOrganisations[3].uuid,
                error: '',
            }
        );

        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        wrapper.destroy();

        expect(spyOnCommit).toHaveBeenCalledWith('organisation/CLEAR_KEEP_ALL');
    });

    it('should unset organisation when there is an address update that changes the region and there is no overwrite', async () => {
        const wrapper = createComponent(
            {
                current: {
                    organisationUuid: mockOrganisations[1].uuid,
                    organisationUuidByPostalCode: mockOrganisations[1].uuid,
                },
                locations: {
                    current: {},
                },
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: [],
                },
            },
            {
                all: mockOrganisations,
                current: mockOrganisations[1],
                currentFromAddressSearch: mockOrganisations[1].uuid,
                error: '',
            },
            {
                loaded: true,
                organisation: {
                    abbreviation: '',
                    bcoPhase: null,
                    hasOutsourceToggle: false,
                    isAvailableForOutsourcing: false,
                    name: '',
                    type: '',
                    uuid: mockOrganisations[1].uuid,
                },
            }
        );

        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        wrapper.vm.$store.commit('organisation/SET_CURRENT_FROM_ADDRESS_SEARCH', mockOrganisations[3].uuid);

        // Wait for store to process update
        await flushCallStack();

        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'organisation/SET_CURRENT', undefined);
    });
});
