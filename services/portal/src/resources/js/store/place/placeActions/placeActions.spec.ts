import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import placeStore from '../placeStore';
import type { PlaceStoreState } from '../placeTypes';
import type { RootStoreState } from '@/store';
import { placeApi } from '@dbco/portal-api';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import * as makeAllCallQueueRequests from '@/components/contextManager/PlacesEdit/SectionManagement/utils/makeAllCallQueueRequests';
import { fakerjs } from '@/utils/test';

const place1 = {
    uuid: '01b63be3-c0dd-4b5e-84d8-a47c3ee7f07a',
    label: 'Apenheul',
    category: 'dieren',
    categoryLabel: 'Dieren',
    indexCountSinceReset: 0,
    indexCountResetAt: null,
    address: {
        street: 'J.C. Wilslaan',
        houseNumber: '21',
        houseNumberSuffix: undefined,
        postalCode: '7313HK',
        town: 'Apeldoorn',
        country: 'NL',
    },
    addressLabel: 'J.C. Wilslaan 21, 7313HK Apeldoorn',
    ggd: {
        code: null,
        name: null,
        municipality: null,
    },
    indexCount: 0,
    isVerified: true,
    organisationUuid: '123456',
    organisationUuidByPostalCode: '1234567',
    source: 'external' as PlaceDTO['source'],
    createdAt: '2021-11-02 13:06:54',
    updatedAt: '2021-11-02 13:06:54',
    lastIndexPresence: fakerjs.date.recent().toString(),
    situationNumbers: [],
    sections: [],
};

const place2 = {
    uuid: '6546765-c0dd-4b5e-84d8-a47c3ee7f07a',
    label: 'Leeuwenheul',
    category: 'dieren',
    categoryLabel: 'Dieren',
    indexCountSinceReset: 0,
    address: {
        street: 'J.C. Wilslaan',
        houseNumber: '22',
        houseNumberSuffix: undefined,
        postalCode: '7313HK',
        town: 'Apeldoorn',
        country: 'NL',
    },
    addressLabel: 'J.C. Wilslaan 22, 7313HK Apeldoorn',
    ggd: {
        code: null,
        name: null,
        municipality: null,
    },
    indexCount: 0,
    isVerified: false,
    source: 'external' as PlaceDTO['source'],
    createdAt: '2021-11-02 13:06:54',
    updatedAt: '2021-11-02 13:06:54',
    lastIndexPresence: fakerjs.date.recent().toString(),
    situationNumbers: [],
    sections: [],
};

const placeSections = [
    { label: 'Entree', uuid: '6d32fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Zonnebank', uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
];

describe('placeActions.ts', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);
    beforeEach(() => {
        vi.restoreAllMocks();
    });
    afterEach(() => {
        vi.clearAllMocks();
    });
    const getStore = (placeStoreState: PlaceStoreState) => {
        const placeStoreModule = {
            ...placeStore,
            state: {
                ...placeStore.state,
                ...placeStoreState,
            },
            actions: placeStore.actions,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                place: placeStoreModule,
            },
        });
    };

    it('should commit SET_PLACE with place from API if place/CREATE is successful', async () => {
        vi.spyOn(placeApi, 'createPlace').mockImplementationOnce(() => Promise.resolve(place1));
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/CREATE', place2);

        expect(spyOnCommit).toHaveBeenCalledWith('place/SET_PLACE', place1, undefined);
    });

    it('should commit SET_PLACE with given place if place/CREATE is unsuccessful', async () => {
        vi.spyOn(placeApi, 'createPlace').mockImplementationOnce(() => Promise.reject());
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/CREATE', place2);

        expect(spyOnCommit).toHaveBeenCalledWith('place/SET_PLACE', place2, undefined);
    });

    it('should commit SET_SECTIONS with sections from API if place/FETCH_SECTIONS is successful', async () => {
        vi.spyOn(placeApi, 'getSections').mockImplementationOnce(() => Promise.resolve({ sections: placeSections }));
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/FETCH_SECTIONS', place1.uuid);

        expect(spyOnCommit).toHaveBeenCalledWith('place/SET_SECTIONS', placeSections, undefined);
    });

    it('should commit UPDATE_PLACE with given place if place/UPDATE is unsuccessful', async () => {
        vi.spyOn(placeApi, 'updatePlace').mockImplementationOnce(() => Promise.reject());
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/UPDATE', place2);

        expect(spyOnCommit).toHaveBeenCalledWith('place/SET_PLACE', place2, undefined);
    });

    it('should commit UPDATE_PLACE with organisationUuid and organisationUuidByPostalCode from API if place/UPDATE is successful', async () => {
        vi.spyOn(placeApi, 'updatePlace').mockImplementationOnce(() => Promise.resolve(place1));
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/UPDATE', place2);

        expect(spyOnCommit).toHaveBeenCalledWith(
            'place/SET_PLACE',
            {
                ...place2,
                ...{
                    organisationUuid: place1.organisationUuid,
                    organisationUuidByPostalCode: place1.organisationUuidByPostalCode,
                },
            },
            undefined
        );
    });

    it('should call "makeAllCallQueueRequests" util when SAVE_SECTIONS is dispatched', async () => {
        vi.spyOn(placeApi, 'updatePlaceSections').mockImplementationOnce(() => Promise.resolve());
        const makeAllCallQueueRequestsSpy = vi.spyOn(makeAllCallQueueRequests, 'default');
        const placeStoreState = {
            current: {
                uuid: '140897272',
            },
            locations: {
                current: {},
            },
            sections: {
                callQueue: {
                    changeLabelQueue: [
                        {
                            label: 'Testhal',
                            uuid: '123',
                        },
                        {
                            label: 'Entreehal',
                            uuid: '234',
                        },
                    ],
                    createQueue: [],
                    mergeQueue: [],
                },
                current: [],
            },
        };
        const store = getStore(placeStoreState);
        await store.dispatch('place/SAVE_SECTIONS');

        expect(makeAllCallQueueRequestsSpy).toHaveBeenCalledWith(
            placeStoreState.current.uuid,
            placeStoreState.sections.callQueue
        );
    });

    it('should NOT call "makeAllCallQueueRequests" util when SAVE_SECTIONS is dispatched without valid place in store', async () => {
        vi.spyOn(placeApi, 'updatePlaceSections').mockImplementationOnce(() => Promise.resolve());
        const makeAllCallQueueRequestsSpy = vi.spyOn(makeAllCallQueueRequests, 'default');
        const placeStoreState = {
            current: {},
            locations: {
                current: {},
            },
            sections: {
                callQueue: {
                    changeLabelQueue: [
                        {
                            label: 'Testhal',
                            uuid: '123',
                        },
                        {
                            label: 'Entreehal',
                            uuid: '234',
                        },
                    ],
                    createQueue: [],
                    mergeQueue: [],
                },
                current: [],
            },
        };
        const store = getStore(placeStoreState);
        await store.dispatch('place/SAVE_SECTIONS');

        expect(makeAllCallQueueRequestsSpy).toHaveBeenCalledTimes(0);
    });

    it('should unverify given place when place/TOGGLE_VERIFICATION is dispatched with verified place', async () => {
        vi.spyOn(placeApi, 'unverify').mockImplementationOnce(() =>
            Promise.resolve({ ...place1, ...{ isVerified: !place1.isVerified } })
        );
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/TOGGLE_VERIFICATION', place1);

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'place/SET_PLACE', place1, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'place/SET_VERIFICATION', false, undefined);
    });

    it('should verify given place when place/TOGGLE_VERIFICATION is dispatched with unverified place', async () => {
        const unverifiedPlace: PlaceDTO = { ...place1, ...{ isVerified: false } };
        vi.spyOn(placeApi, 'verify').mockImplementationOnce(() =>
            Promise.resolve({ ...unverifiedPlace, ...{ isVerified: !unverifiedPlace.isVerified } })
        );
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/TOGGLE_VERIFICATION', unverifiedPlace);

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'place/SET_PLACE', unverifiedPlace, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'place/SET_VERIFICATION', true, undefined);
    });

    it('should NOT verify or unverify given place when place/TOGGLE_VERIFICATION is dispatched if place has no UUID', async () => {
        const placeWithoutUuid: Partial<PlaceDTO> = { ...place1, ...{ uuid: undefined } };
        vi.spyOn(placeApi, 'verify').mockImplementationOnce(() => Promise.resolve(place1));
        const placeStoreState = {
            current: {},
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
        };
        const store = getStore(placeStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('place/TOGGLE_VERIFICATION', placeWithoutUuid);

        expect(spyOnCommit).toHaveBeenCalledTimes(0);
    });
});
