import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import placeStore from '../placeStore';
import type { PlaceStoreState } from '../placeTypes';
import type { RootStoreState } from '@/store';
import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import { fakerjs } from '@/utils/test';

const place: PlaceDTO = {
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

const location: LocationDTO = {
    id: '46345asdfas',
    label: 'Restaurant het Kroontje',
    category: 'restaurant',
    address: {
        street: '',
        houseNumber: '1',
        houseNumberSuffix: '',
        postalCode: '1234AB',
        town: '',
        country: 'NL',
    },
    addressLabel: '1, 1234AB',
    indexCount: 0,
    isVerified: false,
    ggd: { code: null, municipality: null },
};

describe('placeMutations.ts', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (placeStoreState: PlaceStoreState) => {
        const placeStoreModule = {
            ...placeStore,
            state: {
                ...placeStore.state,
                ...placeStoreState,
            },
            mutations: placeStore.mutations,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                place: placeStoreModule,
            },
        });
    };

    it('should add new section to create queue and local state when ADD_SECTION is committed', async () => {
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
        await store.commit('place/ADD_SECTION', 'Random');

        const createQueue = store.state.place.sections.callQueue.createQueue;

        const lastInQueue = createQueue[createQueue.length - 1];

        expect(lastInQueue.label).toBe('Random');

        const currentSections = store.state.place.sections.current;

        const lastInCurrent = currentSections[currentSections.length - 1];

        expect(lastInCurrent.label).toBe('Random');
    });

    it('should update state.locations.current when SET_LOCATION is committed', async () => {
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
        await store.commit('place/SET_LOCATION', location);

        expect(store.state.place.locations.current).toBe(location);
    });

    it('should update state.current when SET_PLACE is committed', async () => {
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
        await store.commit('place/SET_PLACE', place);

        expect(store.state.place.current).toBe(place);
    });

    it('should update state.current.organisationUuid when SET_ORGANISATION is committed', async () => {
        const placeStoreState = {
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
        };
        const store = getStore(placeStoreState);
        expect(store.state.place.current.organisationUuid).toBe(place.organisationUuid);
        await store.commit('place/SET_ORGANISATION', '3987543');

        expect(store.state.place.current.organisationUuid).toBe('3987543');
    });

    it('should update state.current.organisationUuidByPostalCode when SET_ORGANISATION_BY_POSTALCODE is committed', async () => {
        const placeStoreState = {
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
        };
        const store = getStore(placeStoreState);
        expect(store.state.place.current.organisationUuidByPostalCode).toBe(place.organisationUuidByPostalCode);
        await store.commit('place/SET_ORGANISATION_BY_POSTALCODE', '3987543');

        expect(store.state.place.current.organisationUuidByPostalCode).toBe('3987543');
    });

    it('should update state.current.isVerified when SET_VERIFICATION is committed', async () => {
        const placeStoreState = {
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
        };
        const store = getStore(placeStoreState);
        await store.commit('place/SET_VERIFICATION', false);

        expect(store.state.place.current.isVerified).toBe(false);
    });
});
