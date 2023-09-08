import type { CallcenterSearchResult } from '@dbco/portal-api/callcenter.dto';
import { fakerjs } from '@/utils/test';
import { createFakeDataGenerator } from './createFakeDataGenerator';

export const fakeSearchResultWithoutTestDate = createFakeDataGenerator<CallcenterSearchResult>(() => ({
    uuid: fakerjs.string.uuid(),
    token: fakerjs.string.uuid(),
    caseType: 'index',
    personalDetails: [
        { key: 'dateOfBirth', value: fakerjs.date.past().toDateString(), isMatch: true },
        { key: 'lastThreeBsnDigits', value: fakerjs.string.numeric(3), isMatch: true },
        { key: 'address', value: fakerjs.location.streetAddress(), isMatch: true },
    ],
}));

export const fakeSearchResult = createFakeDataGenerator<CallcenterSearchResult>(() => ({
    uuid: fakerjs.string.uuid(),
    token: fakerjs.string.uuid(),
    caseType: 'index',
    testDate: fakerjs.date.past().toDateString(),
    personalDetails: [
        { key: 'dateOfBirth', value: fakerjs.date.past().toDateString(), isMatch: true },
        { key: 'lastThreeBsnDigits', value: fakerjs.string.numeric(3), isMatch: true },
        { key: 'address', value: fakerjs.location.streetAddress(), isMatch: true },
    ],
}));
