import type { PlaceDTO } from '@dbco/portal-api/place.dto';

import formatAddress from './formatAddress';

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

const addressWithStreet = {
    street: 'Teststraat',
    houseNumber: '',
    houseNumberSuffix: '',
    postalCode: '',
    town: 'Test',
    country: 'NL',
};

const addressWithNumber = {
    street: '',
    houseNumber: '1',
    houseNumberSuffix: '',
    postalCode: '',
    town: 'Test',
    country: 'NL',
};

const addressWithSuffix = {
    street: '',
    houseNumber: '1',
    houseNumberSuffix: 'A',
    postalCode: '',
    town: 'Test',
    country: 'NL',
};

const addressWithPostalCode = {
    street: '',
    houseNumber: '1',
    houseNumberSuffix: 'A',
    postalCode: '1234AB',
    town: '',
    country: 'NL',
};

describe('formatAddress', () => {
    it('should return place when its address is null', () => {
        expect(formatAddress(initPlace)).toStrictEqual(initPlace);
    });

    it.each([
        ['street', addressWithStreet],
        ['houseNumber', addressWithNumber],
        ['houseNumberSuffix', addressWithSuffix],
        ['postalCode', addressWithPostalCode],
    ])('should format address with "%s"', (addressType, address) => {
        const place = { ...initPlace, ...{ address } };
        const formattedAddressLabel =
            `${address.street} ${address.houseNumber} ${address.houseNumberSuffix}`.trim() +
            `, ${address.postalCode} ${address.town}`.trim();
        const formattedPlace = { ...place, ...{ addressLabel: formattedAddressLabel } };
        expect(formatAddress(place)).toStrictEqual(formattedPlace);
    });
});
