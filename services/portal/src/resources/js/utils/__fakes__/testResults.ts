import { fakerjs } from '@/utils/test';
import { createFakeDataGenerator } from './createFakeDataGenerator';
import { TestResultSourceV1, TestResultTypeOfTestV1, TestResultResultV1 } from '@dbco/enum';
import type { CreateManualTestResultFields, TestResult } from '@dbco/portal-api/case.dto';

export const fakeTestResultWithoutOptionalFields = createFakeDataGenerator<TestResult>(() => ({
    uuid: fakerjs.string.uuid(),
    dateOfTest: fakerjs.date.past().toDateString(),
    source: fakerjs.helpers.arrayElement([
        TestResultSourceV1.VALUE_coronit,
        TestResultSourceV1.VALUE_meldportaal,
        TestResultSourceV1.VALUE_publicWebPortal,
    ]),
    receivedAt: fakerjs.date.past().toDateString(),
    result: fakerjs.helpers.arrayElement([TestResultResultV1.VALUE_positive, TestResultResultV1.VALUE_negative]),
    typeOfTest: fakerjs.helpers.arrayElement([
        TestResultTypeOfTestV1.VALUE_antigen,
        TestResultTypeOfTestV1.VALUE_pcr,
        TestResultTypeOfTestV1.VALUE_selftest,
    ]),
}));

export const fakeTestResult = createFakeDataGenerator<TestResult>(() => ({
    ...fakeTestResultWithoutOptionalFields(),
    dateOfResult: fakerjs.date.past().toDateString(),
    testLocation: fakerjs.company.name(),
    sampleLocation: fakerjs.company.name(),
    sampleNumber: fakerjs.string.sample(9),
}));

export const fakeCreateManualTestResult = createFakeDataGenerator<CreateManualTestResultFields>(() => ({
    receivedAt: fakerjs.date.past().toDateString(),
    typeOfTest: fakerjs.helpers.arrayElement([
        TestResultTypeOfTestV1.VALUE_antigen,
        TestResultTypeOfTestV1.VALUE_pcr,
        TestResultTypeOfTestV1.VALUE_selftest,
    ]),
    result: fakerjs.helpers.arrayElement([
        TestResultResultV1.VALUE_negative,
        TestResultResultV1.VALUE_positive,
        TestResultResultV1.VALUE_unknown,
    ]),
    dateOfTest: fakerjs.date.recent(),
}));
