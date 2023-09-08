import Vuex from 'vuex';

import { shallowMount } from '@vue/test-utils';
import PlaceSectionsForm from './PlaceSectionsForm.vue';
import placeStore from '@/store/place/placeStore';
import { placeActions, PlaceActions } from '@/store/place/placeActions/placeActions';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const place = {
    uuid: '01b63be3-c0dd-4b5e-84d8-a47c3ee7f07a',
    label: 'Apenheul',
    category: 'dieren',
    categoryLabel: 'Dieren',
    address: {
        street: 'J.C. Wilslaan',
        houseNumber: '21',
        houseNumberSuffix: null,
        postalCode: '7313HK',
        town: 'Apeldoorn',
        country: 'NL',
    },
    addressLabel: 'J.C. Wilslaan 21, 7313HK Apeldoorn',
    ggd: {
        name: null,
        municipality: null,
    },
    indexCount: 0,
    isVerified: false,
    source: 'external',
    createdAt: '2021-11-02 13:06:54',
    updatedAt: '2021-11-02 13:06:54',
};

vi.mock('@dbco/portal-api/client/place.api', () => ({
    getSections: vi.fn((mockResolve) => Promise.resolve({ mockResolve })),
}));

const placeStoreModule = {
    ...placeStore,
    state: {
        ...placeStore.state,
    },
    actions: {
        ...placeActions,
        [PlaceActions.FETCH_SECTIONS]: vi.fn(),
    },
};

const createComponent = setupTest((localVue: VueConstructor, data: Record<string, unknown> = {}) => {
    return shallowMount<PlaceSectionsForm>(PlaceSectionsForm, {
        localVue,
        data: () => data,
        propsData: {
            place,
        },
        store: new Vuex.Store({
            modules: {
                place: placeStoreModule,
            },
        }),
        stubs: {
            SectionManager: true,
        },
    });
});

describe('PlaceSectionsForm.vue', () => {
    it('should dispatch place/SAVE_SECTIONS on save', async () => {
        const data = {
            errors: {},
            values: place,
        };

        const wrapper = createComponent(data);
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        const btn = wrapper.findComponent({ name: 'BButton' });

        await btn.trigger('click');

        await wrapper.vm.$nextTick();

        expect(spyOnDispatch).toHaveBeenCalledWith('place/SAVE_SECTIONS');
    });
});
