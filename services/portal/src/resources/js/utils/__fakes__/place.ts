import type { PlaceCasesResponse, PlaceCasesTable, PlaceDTO } from '@dbco/portal-api/place.dto';
import { contextCategoryV1Options, ContextRelationshipV1, HospitalReasonV1, YesNoUnknownV1 } from '@dbco/enum';
import { fakerjs } from '../test';
import { fakeAddress } from './address';
import { fakeSituation } from './situation';
import { createFakeDataGenerator } from './createFakeDataGenerator';
import { fakeCalendarDateRange } from './calendarDateRange';

const defaultSource = 'external';

export const fakePlace = (): PlaceDTO => ({
    address: fakeAddress(),
    addressLabel: fakerjs.location.streetAddress(),
    category: contextCategoryV1Options[0].value,
    categoryLabel: contextCategoryV1Options[0].label,
    createdAt: fakerjs.date.past().toISOString(),
    ggd: {
        code: null,
        municipality: null,
    },
    label: fakerjs.lorem.word(),
    lastIndexPresence: fakerjs.date.past().toISOString(),
    indexCount: fakerjs.number.int({ min: 1, max: 1000 }),
    indexCountSinceReset: fakerjs.number.int({ min: 1, max: 100 }),
    indexCountResetAt: fakerjs.date.recent().toISOString(),
    isVerified: fakerjs.datatype.boolean(),
    organisationUuid: fakerjs.string.uuid(),
    organisationUuidByPostalCode: fakerjs.string.uuid(),
    source: defaultSource,
    updatedAt: fakerjs.date.recent().toISOString(),
    uuid: fakerjs.string.uuid(),
    situationNumbers: [fakeSituation()],
    sections: [fakerjs.lorem.word()],
});

export const fakePlaceCase = createFakeDataGenerator<PlaceCasesResponse>(() => {
    const firstName = fakerjs.person.firstName();
    const lastName = fakerjs.person.lastName();
    return {
        canChangeOrganisation: true,
        caseId: fakerjs.string.uuid(),
        causeForConcern: YesNoUnknownV1.VALUE_no,
        createdAt: fakerjs.date.past().toISOString(),
        dateOfBirth: fakerjs.date.past().toISOString(),
        dateOfSymptomOnset: fakerjs.date.recent().toISOString(),
        dateOfTest: fakerjs.date.past().toISOString(),
        firstName,
        lastName,
        name: `${firstName} ${lastName}`,
        isDeceased: YesNoUnknownV1.VALUE_no,
        mostRecentVaccinationDate: fakerjs.date.past().toISOString(),
        notificationNamedConsent: true,
        moments: [fakeCalendarDateRange()],
        symptoms: {
            hasSymptoms: YesNoUnknownV1.VALUE_yes,
            stillHadSymptomsAt: fakerjs.date.past().toISOString(),
        },
        hospital: {
            reason: HospitalReasonV1.VALUE_covid,
            isAdmitted: YesNoUnknownV1.VALUE_yes,
        },
        relationContext: ContextRelationshipV1.VALUE_teacher,
        sections: fakerjs.custom.typedArray<string>(fakerjs.location.secondaryAddress()),
        token: fakerjs.string.uuid(),
        uuid: fakerjs.string.uuid(),
        vaccinationCount: fakerjs.number.int({ min: 1, max: 2 }),
    };
});

export const fakePlaceCaseTable = createFakeDataGenerator<PlaceCasesTable>(() => ({
    infiniteId: fakerjs.number.int(),
    page: 1,
    perPage: fakerjs.number.int(),
    fetchedPages: [],
}));
