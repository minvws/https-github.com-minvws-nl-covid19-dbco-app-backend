import faker from '../utils/faker-decorator';
import { toDateString } from '../utils/date-format';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';

export const createContext = (data: Partial<PlaceDTO>) => {
    const body = givenContext(data);

    return cy
        .authenticatedAPIRequest<CreatedContext>({
            method: 'POST',
            url: `/api/places`,
            body,
        })
        .its('body');
};

export const givenContext = (partialContextData: Partial<PlaceDTO> = {}): Partial<PlaceDTO> => {
    const lastIndexPresenceDate = faker.date.past(faker.number.int({ min: 10, max: 60 }));
    return {
        label: faker.lorem.words(),
        category: faker.helpers.arrayElement(['vlieg_transport', 'kinder_opvang', 'zwembad', 'retail']),
        categoryLabel: null,
        address: {
            postalCode: faker.location.zipCode('####??'),
            street: faker.location.streetAddress(),
            houseNumber: faker.location.buildingNumber(),
            houseNumberSuffix: faker.helpers.arrayElement(['a', 'bs']),
            town: faker.location.cityName(),
            country: faker.location.countryCode(),
        },
        addressLabel: `${faker.location.buildingNumber()}, ${faker.location.zipCode('####??')}`,
        isVerified: true,
        organisationUuid: '00000000-0000-0000-0000-000000000000',
        source: 'manual',
        lastIndexPresence: toDateString(lastIndexPresenceDate),
        indexCount: faker.number.int({ min: 10, max: 100 }),
        indexCountSinceReset: faker.number.int({ min: 10, max: 100 }),
        indexCountResetAt: toDateString(faker.date.recent()),
        sections: ['ziekenhuis', 'school'],
        situationNumbers: [
            {
                uuid: faker.string.uuid(),
                value: faker.number.int({ min: 1000, max: 10000 }).toString(),
                name: faker.lorem.words(1),
            },
        ],
        ...partialContextData,
    };
};
export type CreatedContext = Record<string, unknown> & {
    uuid: string;
    label: string;
    createdAt: string;
    updatedAt: string;
};
