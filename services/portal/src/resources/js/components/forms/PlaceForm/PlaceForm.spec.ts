import Vuex from 'vuex';
import { PermissionV1 } from '@dbco/enum';

import placeStore from '@/store/place/placeStore';
import organisationStore from '@/store/organisation/organisationStore';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';

import { shallowMount } from '@vue/test-utils';

import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';

import PlaceForm from '@/components/forms/PlaceForm/PlaceForm.vue';
import type { PlaceStoreState } from '@/store/place/placeTypes';
import type { OrganisationStoreState } from '@/store/organisation/organisationTypes';

import * as checkForDuplicates from '@/components/forms/formUtils/checkForDuplicates/checkForDuplicates';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

vi.mock('@dbco/portal-api/client/place.api', () => ({
    createPlace: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
    updatePlace: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
}));

const place: Partial<PlaceDTO> = {
    uuid: 'aa78c642-399f-468a-b625-8ecc395fa3fa',
    label: 'Ristorante Corona',
    indexCount: 0,
    category: 'restaurant',
    addressLabel: '1, 1234AB',
    address: {
        country: '',
        street: '',
        houseNumber: '1',
        houseNumberSuffix: undefined,
        postalCode: '1234AB',
        town: '',
    },
    ggd: {
        code: null,
        municipality: null,
    },
    organisationUuidByPostalCode: null,
    organisationUuid: null,
    isVerified: false,
};

const location: Partial<LocationDTO> = {
    id: 'aa78c642-399f-468a-b625-8ecc395fa3fa',
    label: 'Ristorante Corona',
    indexCount: 0,
    category: 'restaurant',
    addressLabel: '1, 1234AB',
    address: {
        country: '',
        street: '',
        houseNumber: '1',
        houseNumberSuffix: undefined,
        postalCode: '1234AB',
        town: '',
    },
    ggd: {
        code: null,
        municipality: null,
    },
    isVerified: false,
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        editMode?: boolean,
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
        return shallowMount<PlaceForm>(PlaceForm, {
            localVue,
            propsData: { editMode },
            store: new Vuex.Store({
                modules: {
                    place: placeStoreModule,
                    organisation: organisationStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: {
                BButton: true,
                FormulateForm: true,
                Place: true,
                OrganisationEdit: true,
            },
        });
    }
);

describe('PlaceForm.vue', () => {
    it('should NOT render Place component when editMode is true and formData is based on location', () => {
        const wrapper = createComponent(
            true,
            {
                current: {},
                locations: {
                    current: location,
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
                all: [],
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        const places = wrapper.findAllComponents({ name: 'Place' });
        expect(places.length).toBe(0);
    });

    it('should NOT render OrganisationEdit when user does NOT have permission to verify place', () => {
        const wrapper = createComponent(true);
        const organisationEdit = wrapper.findComponent({ name: 'OrganisationEdit' });
        expect(organisationEdit.exists()).toBe(false);
    });

    it('should NOT render OrganisationEdit when editMode is false and formData is based on location', () => {
        const wrapper = createComponent(
            false,
            {
                current: {},
                locations: {
                    current: location,
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
                all: [],
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            }
        );
        const organisationEdit = wrapper.findComponent({ name: 'OrganisationEdit' });
        expect(organisationEdit.exists()).toBe(false);
    });

    it.each([
        ['Context wijzigen', true, [PermissionV1.VALUE_placeVerify]],
        ['Context aanmaken', false, [PermissionV1.VALUE_placeVerify]],
        ['Context aanmaken', false, [PermissionV1.VALUE_placeMerge]],
    ])('should render button with text "%s" when editMode is %s and user permission is %s', (text, mode, permit) => {
        const wrapper = createComponent(mode, undefined, undefined, {
            loaded: true,
            permissions: permit,
        });

        const button = wrapper.findComponent({ name: 'BButton' });
        expect(button.html()).toContain(text);
    });

    it('should dispatch place/UPDATE when in editMode', async () => {
        vi.spyOn(checkForDuplicates, 'default').mockImplementationOnce(() => Promise.resolve([]));
        const wrapper = createComponent(
            true,
            {
                current: place,
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
                all: [],
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            },
            {
                loaded: true,
                permissions: [PermissionV1.VALUE_placeVerify],
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.submitForm();

        expect(spyOnDispatch).toHaveBeenCalledWith('place/UPDATE', {
            ...place,
            ...{ organisationUuid: undefined },
        });
    });

    it('should dispatch place/CREATE when NOT in editMode', async () => {
        vi.spyOn(checkForDuplicates, 'default').mockImplementationOnce(() => Promise.resolve([]));
        const wrapper = createComponent(
            false,
            {
                current: place,
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
                all: [],
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            },
            {
                loaded: true,
                permissions: [PermissionV1.VALUE_placeVerify],
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.submitForm();

        expect(spyOnDispatch).toHaveBeenCalledWith('place/CREATE', {
            ...place,
            ...{ organisationUuid: undefined },
        });
    });

    it('should not update or create place when place is a duplicate', async () => {
        vi.spyOn(checkForDuplicates, 'default').mockImplementationOnce(() => Promise.resolve([place]));
        const wrapper = createComponent(
            true,
            {
                current: place,
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
                all: [],
                current: undefined,
                currentFromAddressSearch: undefined,
                error: '',
            },
            {
                loaded: true,
                permissions: [PermissionV1.VALUE_placeVerify],
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.submitForm();

        expect(spyOnDispatch).toHaveBeenCalledTimes(0);
    });

    it('should dispatch place/UPDATE with currentFromAddressSearch when an address update changes the region and there is no manually selected organisation', async () => {
        vi.spyOn(checkForDuplicates, 'default').mockImplementationOnce(() => Promise.resolve([]));

        const wrapper = createComponent(
            true,
            {
                current: place,
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
                all: [{ uuid: '65474', name: 'Test' }],
                current: {},
                currentFromAddressSearch: '65474',
                error: '',
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.submitForm();

        expect(spyOnDispatch).toHaveBeenCalledWith('place/UPDATE', {
            ...place,
            ...{ organisationUuid: '65474' },
        });
    });
});
