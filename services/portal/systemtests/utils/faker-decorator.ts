import { faker as fakerjs } from '@faker-js/faker';

export const faker = {
    ...fakerjs,
    case: {
        hpZoneNumber: () => fakerjs.number.int({ min: 1000000, max: 9999999 }).toString(),
    },
    testResult: {
        monsterNumber: () =>
            fakerjs.string.numeric(3) +
            fakerjs.string.alpha() +
            fakerjs.string.numeric({
                length: faker.number.int({ min: 1, max: 12 }),
                allowLeadingZeros: true,
            }),
    },
};
export default faker;
