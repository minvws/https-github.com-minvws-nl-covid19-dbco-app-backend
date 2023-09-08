import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import { placeSchema } from '@/components/form/ts/formSchema';
import { SharedMutations } from '@/store/mutations';
import organisationStore from '@/store/organisation/organisationStore';
import type { OrganisationStoreState } from '@/store/organisation/organisationTypes';
import { PlaceActions, placeActions } from '@/store/place/placeActions/placeActions';
import placeStore from '@/store/place/placeStore';
import type { PlaceStoreState } from '@/store/place/placeTypes';
import { StoreType } from '@/store/storeType';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { flushCallStack, setupTest } from '@/utils/test';
import { fakePlace } from '@/utils/__fakes__/place';
import { setActivePinia, createPinia } from 'pinia';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import PlacesEditModal from './PlacesEditModal.vue';

const place: PlaceDTO = fakePlace();

vi.mock('@dbco/portal-api/client/place.api', () => ({
    getSections: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
    mergeSections: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
    updatePlaceSections: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
    updatePlace: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
}));

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        data: object = {},
        placeStoreState?: Partial<PlaceStoreState>,
        organisationStoreState?: Partial<OrganisationStoreState>,
        userInfoStoreState?: Partial<UserInfoState>
    ) => {
        const placeStoreModule = {
            ...placeStore,
            state: {
                ...placeStore.state,
                ...placeStoreState,
            },
            actions: {
                ...placeActions,
                [PlaceActions.FETCH_SECTIONS]: vi.fn(),
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
        return shallowMount<PlacesEditModal>(PlacesEditModal, {
            localVue,
            data: () => data,
            store: new Vuex.Store({
                modules: {
                    place: placeStoreModule,
                    organisation: organisationStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: {
                FormulateFormWrapper: true,
                SectionManager: true,
            },
        });
    }
);

describe('PlacesEditModal.vue', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should NOT render without existing place', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(false);
    });

    it('should dispatch place/UPDATE with values and organisationUuid + place/SAVE_SECTIONS on save', async () => {
        const data = {
            errors: {},
            values: place,
        };

        const wrapper = createComponent(
            data,
            {
                current: place,
            },
            {
                current: {
                    name: 'Test',
                    uuid: '98765',
                },
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        const modal = wrapper.findComponent({ name: 'BModal' });

        await modal.vm.$emit('ok');

        expect(spyOnDispatch).toHaveBeenCalledWith('place/UPDATE', {
            ...data.values,
            ...{ organisationUuid: '98765' },
            ...{ situationNumbers: [] },
        });

        // Wait for store to process update
        await flushCallStack();

        expect(spyOnDispatch).toHaveBeenCalledWith('place/SAVE_SECTIONS');
    });

    it('should dispatch place/UPDATE with values and currentFromAddressSearch an address update changes the region and there is no manually selected organisation', async () => {
        const data = {
            errors: {},
            values: place,
        };

        const wrapper = createComponent(
            data,
            {
                current: place,
            },
            {
                currentFromAddressSearch: '65474',
            }
        );
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        const modal = wrapper.findComponent({ name: 'BModal' });

        await modal.vm.$emit('ok');

        expect(spyOnDispatch).toHaveBeenCalledWith('place/UPDATE', {
            ...data.values,
            ...{ organisationUuid: '65474' },
            ...{ situationNumbers: [] },
        });
    });

    it(`should commit ${StoreType.PLACE}/${SharedMutations.CLEAR} when destroyed`, () => {
        const data = {
            errors: {},
            values: place,
        };

        const placeStore = {
            current: place,
        };

        const wrapper = createComponent(data, placeStore);
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        wrapper.destroy();

        expect(spyOnCommit).toHaveBeenCalledWith(`${StoreType.PLACE}/${SharedMutations.CLEAR}`);
        expect(wrapper.vm.$store.state.place.current).toEqual({});
    });

    it('should use schema with warning when an address update changes the region and there is no manually selected organisation', () => {
        const data = {
            errors: {},
            values: place,
        };

        const wrapper = createComponent(
            data,
            {
                current: place,
            },
            {
                currentFromAddressSearch: '123457',
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
                    uuid: '123456',
                },
            }
        );

        expect(wrapper.vm.schema).toStrictEqual(placeSchema(true));
    });
});
