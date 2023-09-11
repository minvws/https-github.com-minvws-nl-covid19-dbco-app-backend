import type { Address } from '@/components/form/ts/formTypes';
import { fakerjs } from '@/utils/test';
import { createFakeDataGenerator } from './createFakeDataGenerator';

export const fakeAddress = createFakeDataGenerator<Address>(() => ({
    country: fakerjs.location.countryCode(),
    houseNumber: fakerjs.location.buildingNumber(),
    houseNumberSuffix: fakerjs.string.alpha(),
    postalCode: fakerjs.location.zipCode('#### ??'),
    street: fakerjs.location.street(),
    town: fakerjs.location.county(),
}));
