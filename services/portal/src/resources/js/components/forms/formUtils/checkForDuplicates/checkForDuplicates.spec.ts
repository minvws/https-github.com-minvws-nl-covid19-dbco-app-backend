import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import { placeApi } from '@dbco/portal-api';

import checkForDuplicates from './checkForDuplicates';

const initPlace = {
    label: null,
    category: '',
    categoryLabel: null,
    address: null,
    addressLabel: null,
    ggd: {
        code: null,
        municipality: null,
    },
    uuid: '',
    street: '',
    housenumber: '',
    housenumberSuffix: null,
    postalcode: '',
    town: '',
    country: '',
    indexCount: 0,
    updatedAt: '',
    createdAt: '',
    editable: true,
    isVerified: false,
    source: 'manual' as PlaceDTO['source'],
};

const placeWithAddress = {
    uuid: '46345asdfas',
    label: 'Restaurant het Kroontje',
    category: 'restaurant',
    categoryLabel: null,
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
    source: 'manual' as PlaceDTO['source'],
    ggd: { code: null, municipality: null },
    createdAt: '2022-02-07T11:01:31Z',
    updatedAt: '2022-02-10T14:44:31Z',
};

const locationWithAddress: LocationDTO = {
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

const duplicate1 = {
    uuid: '5a160898-5b71-42f6-9506-04735823066d',
    label: 'Restaurant het Kroontje',
    category: 'restaurant',
    categoryLabel: null,
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
    source: 'manual' as PlaceDTO['source'],
    ggd: {
        code: null,
        municipality: null,
    },
    createdAt: '2022-02-07T11:01:31Z',
    updatedAt: '2022-02-10T14:44:31Z',
};

const duplicate2 = {
    uuid: '46345asdfas',
    label: 'Hotel Kroon',
    category: 'restaurant',
    categoryLabel: null,
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
    source: 'manual' as PlaceDTO['source'],
    ggd: {
        code: null,
        municipality: null,
    },
    createdAt: '2022-02-07T11:01:31Z',
    updatedAt: '2022-02-10T14:44:31Z',
};

const addressWithDifferingNumber = {
    street: '',
    houseNumber: '12',
    houseNumberSuffix: '',
    postalCode: '1234AB',
    town: '',
    country: 'NL',
};

const addressWithDifferingSuffix = {
    street: '',
    houseNumber: '1',
    houseNumberSuffix: 'C',
    postalCode: '1234AB',
    town: '',
    country: 'NL',
};

const addressWithDifferingPostal = {
    street: '',
    houseNumber: '1',
    houseNumberSuffix: '',
    postalCode: '1234AC',
    town: '',
    country: 'NL',
};

const addressWithShorterPostal = { ...addressWithDifferingPostal, ...{ postalCode: '1234' } };

describe('checkForDuplicates', () => {
    it('should return empty array when place address is null', async () => {
        const duplicates = await checkForDuplicates(initPlace);
        expect(duplicates).toStrictEqual([]);
    });

    it('should return empty array when search returns no duplicates', async () => {
        vi.spyOn(placeApi, 'search').mockImplementationOnce(() => Promise.resolve({ places: [] }));
        const duplicates = await checkForDuplicates(placeWithAddress);
        expect(duplicates).toStrictEqual([]);
    });

    it('should return empty array when search returns place with same id', async () => {
        vi.spyOn(placeApi, 'search').mockImplementationOnce(() =>
            Promise.resolve({
                places: [duplicate2],
                suggestions: [],
            })
        );
        const duplicates = await checkForDuplicates(placeWithAddress);
        expect(duplicates).toStrictEqual([]);
    });

    it('should return places from search with matching address as duplicates: place', async () => {
        vi.spyOn(placeApi, 'search').mockImplementationOnce(() =>
            Promise.resolve({
                places: [duplicate1],
                suggestions: [],
            })
        );
        const duplicates = await checkForDuplicates(placeWithAddress);
        expect(duplicates).toStrictEqual([duplicate1]);
    });

    it('should return places from search with matching address as duplicates: location', async () => {
        vi.spyOn(placeApi, 'search').mockImplementationOnce(() =>
            Promise.resolve({
                places: [duplicate1],
                suggestions: [],
            })
        );
        const duplicates = await checkForDuplicates(locationWithAddress);
        expect(duplicates).toStrictEqual([duplicate1]);
    });

    it.each([
        ['houseNumber', addressWithDifferingNumber],
        ['houseNumberSuffix', addressWithDifferingSuffix],
        ['postalCode', addressWithDifferingPostal],
        ['postalCodeLength', addressWithShorterPostal],
    ])('should return empty array if duplicate has differing "%s"', async (addressType, address) => {
        vi.spyOn(placeApi, 'search').mockImplementationOnce(() =>
            Promise.resolve({
                places: [{ ...duplicate1, ...{ address } }],
                suggestions: [],
            })
        );
        const duplicates = await checkForDuplicates(placeWithAddress);
        expect(duplicates).toStrictEqual([]);
    });
});
