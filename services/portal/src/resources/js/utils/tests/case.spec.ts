import { isNo, isUnknown, isYes } from '@/components/form/ts/formOptions';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { YesNoUnknownV1, HospitalReasonV1, InformStatusV1 } from '@dbco/enum';
import type { Task } from '@dbco/portal-api/task.dto';
import { TaskStatus, TaskGroup } from '@dbco/portal-api/task.dto';
import {
    assumedStillHadSymptomsAt,
    assumedWasSymptomaticAtTimeOfCall,
    assumptionHospitalIsAdmittedUnknown,
    assumptionHospitalReasonUnknown,
    canDetermineInfectiousPeriod,
    determineIsolationDay,
    getIsolationAdviceSymptomatic,
    getTaskLastContactDateWarning,
    infectiousDates,
    isHospitalizedForCovid,
    isMedicalPeriodInfoIncomplete,
    isMedicalPeriodInfoNotDefinitive,
    isSymptomatic,
    sourceDates,
} from '../case';

beforeAll(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2021, 0, 1));
});

describe('determineIsolationDay', () => {
    it('should return null when fragments are not set', () => {
        const result = determineIsolationDay({} as any);

        expect(result).toEqual(null);
    });

    /**
     * These combinations follow Confluence calendar logic schema and can be updated as the schema gets updated.
     *
     * By defining the track which is being tested, it should be possible to follow the track on the schema.
     * All variables not defined, will be treated as undefined.
     */
    const combinations = [
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-11', // dateOfSymptomOnset + 10
            hasSymptoms: isYes,
            hospitalizedReason: HospitalReasonV1.VALUE_covid,
            isHospitalized: isYes,
            track: '1: Hospitalized: Yes | Because of COVID-19: Yes | EZD: Known',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-06', // dateOfSymptomOnset + 5
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-04', // in the schema this is 'today' but it's actually stored in the fragments by the FE
            wasSymptomaticAtTimeOfCall: isYes,
            track: '2: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: No | Today: within default isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-10', // stillHadSymptomsAt + 1
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-09', // in the schema this is 'today' but it's actually stored in the fragments by the FE
            track: '3: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Not answered (No) | Today: after default isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-11', // dateOfSymptomOnset + 10 (maximum possible)
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-16', // in the schema this is 'today' but it's actually stored in the fragments by the FE
            track: '4: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Not answered (No) | Today: after max isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-06', // stillHadSymptomsAt + 5
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-04',
            wasSymptomaticAtTimeOfCall: isNo,
            track: '5: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Ja | stillHadSymptomsAt: within default isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-06', // stillHadSymptomsAt + 5
            hasSymptoms: isYes,
            wasSymptomaticAtTimeOfCall: isNo,
            track: '6: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Ja | stillHadSymptomsAt: Not answered',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-10', // stillHadSymptomsAt + 1
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-09',
            wasSymptomaticAtTimeOfCall: isNo,
            track: '7: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Ja | stillHadSymptomsAt: after default isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-11', // stillHadSymptomsAt + 10 (maximum possible)
            hasSymptoms: isYes,
            stillHadSymptomsAt: '2021-09-16',
            wasSymptomaticAtTimeOfCall: isNo,
            track: '8: Hospitalized: No | Complaints: Yes | EZD: Known | Klachten voorbij?: Ja | stillHadSymptomsAt: after max isolation period',
        },
        {
            dateOfSymptomOnset: '2021-09-01',
            dateOfTest: '2021-09-03',
            expectation: '2021-09-08', // dateOfTest + 5
            hasSymptoms: isNo,
            track: '9: Hospitalized: No | Complaints: No | Testdatum: Known',
        },
    ];

    it.concurrent.each(combinations)(
        'should return $expectation for track $track',
        ({
            dateOfSymptomOnset,
            dateOfTest,
            expectation,
            hasSymptoms = null,
            hospitalizedReason = null,
            isHospitalized = null,
            stillHadSymptomsAt = null,
            wasSymptomaticAtTimeOfCall = null,
        }) => {
            const covidCase = {
                hospital: {
                    isAdmitted: isHospitalized,
                    reason: hospitalizedReason,
                },
                symptoms: {
                    hasSymptoms,
                    wasSymptomaticAtTimeOfCall,
                    stillHadSymptomsAt,
                },
                test: {
                    dateOfSymptomOnset,
                    dateOfTest,
                },
            } as CovidCaseUnionDTO;

            const isolationDay = determineIsolationDay(covidCase);

            expect(isolationDay).toEqual(new Date(expectation));
        }
    );
});

describe('getIsolationAdviceSymptomatic', () => {
    it('should add 6 days and 10 days to passed date', () => {
        const actual = getIsolationAdviceSymptomatic(new Date('2021-09-01'));

        const expectedOutputSixDays = 'dinsdag 7 sep.';
        const expectedOutputTenDays = 'zaterdag 11 sep.';
        const expectedSentence = `Isolatie symptomatische index: thuis blijven tot en met ${expectedOutputTenDays} Index mag eventueel vanaf ${expectedOutputSixDays} naar buiten, als de index 24 uur klachtenvrij is en niet in het ziekenhuis is opgenomen.`;

        expect(actual).toEqual(expectedSentence);
    });
});

describe('sourceDates', () => {
    it('should return null when dateOfSymptomOnset is not set', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
        } as CovidCaseUnionDTO;

        const range = sourceDates(covidCase);

        expect(range).toEqual(null);
    });

    it('should return range ending dateOfSymptomOnset - 2 days when not symptomatic', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const range = sourceDates(covidCase);

        expect(range).toEqual({
            startDate: new Date('2021-03-18'),
            endDate: new Date('2021-03-30'),
        });
    });

    it('should return range ending dateOfSymptomOnset - 1 days when symptomatic', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isNo,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const range = sourceDates(covidCase);

        expect(range).toEqual({
            startDate: new Date('2021-03-18'),
            endDate: new Date('2021-03-31'),
        });
    });
});

describe('infectiousDates', () => {
    it('should return null when isolation day can not be calculated', () => {
        const covidCase = {
            symptoms: {},
            hospital: { isAdmitted: isNo, reason: null },
        } as CovidCaseUnionDTO;

        const ranges = infectiousDates(covidCase);

        expect(ranges).toEqual(null);
    });

    it('should use dateOfTest when not symptomatic', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isNo,
                wasSymptomaticAtTimeOfCall: null,
            },
            hospital: { isAdmitted: isNo, reason: null },
            immunity: { isImmune: null },
            test: {
                dateOfTest: '2021-04-02',
            },
        } as CovidCaseUnionDTO;

        const ranges = infectiousDates(covidCase);

        expect(ranges).toEqual({
            endDate: new Date('2021-04-07'),
            startDate: new Date('2021-04-02'),
        });
    });

    it('should use dateOfSymptomOnset and have longer infectious range when symptomatic', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
                wasSymptomaticAtTimeOfCall: null,
            },
            hospital: { isAdmitted: isNo, reason: null },
            test: {
                dateOfTest: '2021-04-02',
                dateOfSymptomOnset: '2021-04-01',
            },
            medication: {
                isImmunoCompromised: isNo,
            },
            underlyingSuffering: {
                hasUnderlyingSufferingOrMedication: isNo,
            },
        } as CovidCaseUnionDTO;

        const ranges = infectiousDates(covidCase);

        expect(ranges).toEqual({
            endDate: new Date('2021-04-06'),
            startDate: new Date('2021-03-30'),
        });
    });
});

describe('isSymptomatic', () => {
    it('should return null when fragments are not set', () => {
        const result = isSymptomatic({} as any);

        expect(result).toEqual(null);
    });

    it('should return null when hasSymptoms is not set', () => {
        const covidCase = {
            symptoms: {},
        } as CovidCaseUnionDTO;

        const result = isSymptomatic(covidCase);

        expect(result).toEqual(null);
    });

    it('should return null when hasSymptoms is "unknown"', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isUnknown,
            },
        } as CovidCaseUnionDTO;

        const result = isSymptomatic(covidCase);

        expect(result).toEqual(null);
    });

    it('should return true when hasSymptoms is "yes"', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
        } as CovidCaseUnionDTO;

        const result = isSymptomatic(covidCase);

        expect(result).toEqual(true);
    });

    it('should return false when hasSymptoms is "no"', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isNo,
            },
        } as CovidCaseUnionDTO;

        const result = isSymptomatic(covidCase);

        expect(result).toEqual(false);
    });
});

describe('isMedicalPeriodInfoIncomplete', () => {
    it('should return true when fragments are not set', () => {
        const result = isMedicalPeriodInfoIncomplete(null as unknown as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return true when hasSymptoms is not set', () => {
        const result = isMedicalPeriodInfoIncomplete({ symptoms: {} } as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return true when hasSymptoms is unknown', () => {
        const result = isMedicalPeriodInfoIncomplete({
            symptoms: {
                hasSymptoms: isUnknown,
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return true when symptomatic and no dateOfSymptomOnset', () => {
        const result = isMedicalPeriodInfoIncomplete({
            symptoms: {
                hasSymptoms: isYes,
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return true when asymptomatic and no dateOfTest', () => {
        const result = isMedicalPeriodInfoIncomplete({
            symptoms: {
                hasSymptoms: isNo,
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return false when asymptomatic with dateOfTest', () => {
        const result = isMedicalPeriodInfoIncomplete({
            symptoms: {
                hasSymptoms: isNo,
            },
            test: {
                dateOfTest: '2021-09-01',
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(false);
    });

    it('should return true when isAdmitted isYes, reason is covid and dateOfSymptomOnset is null or undefined', () => {
        const result = isMedicalPeriodInfoIncomplete({
            hospital: {
                isAdmitted: isYes,
                reason: HospitalReasonV1.VALUE_covid,
            },
            test: {
                dateOfSymptomOnset: null,
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(true);
    });

    it('should return false when isAdmitted isYes, reason is covid and dateOfSymptomOnset is defined', () => {
        const result = isMedicalPeriodInfoIncomplete({
            hospital: {
                isAdmitted: isYes,
                reason: HospitalReasonV1.VALUE_covid,
            },
            test: {
                dateOfSymptomOnset: '2021-09-01',
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(false);
    });

    it('should return false when asymptomatic with dateOfTest and immunity', () => {
        const result = isMedicalPeriodInfoIncomplete({
            symptoms: {
                hasSymptoms: isNo,
            },
            immunity: {
                isImmune: isNo,
            },
            test: {
                dateOfTest: '2021-09-01',
            },
        } as CovidCaseUnionDTO);

        expect(result).toEqual(false);
    });
});

describe('isHospitalizedforCovid', () => {
    const combinations = [
        // First case is to check if it handles a potential undefined correctly.
        {
            caseFragments: {},
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: null,
                    reason: null,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: null,
                },
            },
            expectation: false,
        },
        // this next one should not be possible in the application, testing it's result just in case.
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: HospitalReasonV1.VALUE_covid,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_other,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_unknown,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: null,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_covid,
                },
            },
            expectation: true,
        },
    ];

    combinations.forEach((combination) => {
        it(`should return ${combination.expectation} when hospital.isAdmitted is ${combination.caseFragments?.hospital?.isAdmitted} and hospital.reason ${combination.caseFragments?.hospital?.reason}`, () => {
            expect(isHospitalizedForCovid(combination.caseFragments as CovidCaseUnionDTO)).toBe(
                combination.expectation
            );
        });
    });
});

describe('isMedicalPeriodInfoNotDefinitive', () => {
    // Will not test all combinations, those are tested in their respective tests.
    // This will only test a true/false version
    const combinations = [
        // 0 First case is to check if it handles a potential undefined correctly.
        {
            caseFragments: {},
            expectation: true,
        },
        // 1 no casefragments
        {
            caseFragments: null,
            expectation: true,
        },
        // 2 assumptionHospitalIsAdmittedUnknown true
        {
            caseFragments: {
                hospital: {
                    isAdmitted: null,
                    reason: null,
                },
                symptoms: {
                    hasSymptoms: null,
                },
                test: {
                    dateOfSymptomOnset: null,
                    dateOfTest: null,
                },
                underlyingSuffering: {
                    hasUnderlyingSufferingOrMedication: null,
                },
                medication: {
                    isImmunoCompromised: null,
                },
                immunity: { isImmune: null },
            },
            expectation: true,
        },
        // 3 assumptionHospitalReasonUnknown true
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_unknown,
                },
                symptoms: {
                    hasSymptoms: null,
                },
                test: {
                    dateOfSymptomOnset: null,
                    dateOfTest: null,
                },
                underlyingSuffering: {
                    hasUnderlyingSufferingOrMedication: null,
                },
                medication: {
                    isImmunoCompromised: null,
                },
                immunity: { isImmune: null },
            },
            expectation: true,
        },
        // 4 assumptionUnderlyingSufferingUnknown true
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_other,
                },
                symptoms: {
                    hasSymptoms: isYes,
                    wasSymptomaticAtTimeOfCall: null,
                },
                test: {
                    dateOfSymptomOnset: new Date(),
                    dateOfTest: null,
                },
                underlyingSuffering: {
                    hasUnderlyingSufferingOrMedication: null,
                },
                medication: {
                    isImmunoCompromised: null,
                },
                immunity: { isImmune: null },
            },
            expectation: true,
        },
        // 5 assumptionsImmuneUnknown - no longer has influence DBCO-3442
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_other,
                },
                symptoms: {
                    hasSymptoms: isNo,
                },
                test: {
                    dateOfSymptomOnset: null,
                    dateOfTest: new Date(),
                },
                underlyingSuffering: {
                    hasUnderlyingSufferingOrMedication: null,
                },
                medication: {
                    isImmunoCompromised: null,
                },
                immunity: { isImmune: null },
            },
            expectation: false,
        },
        // 6 All false
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_other,
                },
                symptoms: {
                    hasSymptoms: null,
                },
                test: {
                    dateOfSymptomOnset: new Date(),
                    dateOfTest: null,
                },
                underlyingSuffering: {
                    hasUnderlyingSufferingOrMedication: null,
                },
                medication: {
                    isImmunoCompromised: null,
                },
                immunity: { isImmune: null },
            },
            expectation: false,
        },
    ];
    combinations.forEach((combination, index) => {
        // no neat testcase description here, too many variables.
        it(`should return ${combination.expectation} when scenario ${index} is followed`, () => {
            expect(isMedicalPeriodInfoNotDefinitive(combination.caseFragments as CovidCaseUnionDTO)).toBe(
                combination.expectation
            );
        });
    });
});

describe('assumptionHospitalIsAdmittedUnknown', () => {
    const combinations = [
        {
            caseFragments: {
                hospital: {
                    isAdmitted: null,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: undefined,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isUnknown,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                },
            },
            expectation: false,
        },
    ];

    combinations.forEach((combination) => {
        it(`should return ${combination.expectation} if isAdmitted is ${combination.caseFragments.hospital.isAdmitted}`, () => {
            expect(assumptionHospitalIsAdmittedUnknown(combination.caseFragments as CovidCaseUnionDTO)).toBe(
                combination.expectation
            );
        });
    });
});

describe('assumptionHospitalReasonUnknown', () => {
    const combinations = [
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: null,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: undefined,
                },
            },
            expectation: true,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_unknown,
                },
            },
            expectation: true,
        },
        // Does not lead to an assumption
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_covid,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isYes,
                    reason: HospitalReasonV1.VALUE_other,
                },
            },
            expectation: false,
        },
        // isNo Track, does not lead to an assumption
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: null,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: null,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: HospitalReasonV1.VALUE_unknown,
                },
            },
            expectation: false,
        },
        {
            caseFragments: {
                hospital: {
                    isAdmitted: isNo,
                    reason: HospitalReasonV1.VALUE_other,
                },
            },
            expectation: false,
        },
    ];

    combinations.forEach((combination) => {
        it(`should return ${combination.expectation} if isAdmitted is ${combination.caseFragments.hospital.isAdmitted} and reason is ${combination.caseFragments.hospital.reason}`, () => {
            expect(assumptionHospitalReasonUnknown(combination.caseFragments as CovidCaseUnionDTO)).toBe(
                combination.expectation
            );
        });
    });
});

describe('assumedWasSymptomaticAtTimeOfCall', () => {
    it('should return true if wasSymptomaticAtTimeOfCall was assumed because wasSymptomaticAtTimeOfCall is not set', () => {
        const caseFragments = {
            symptoms: { hasSymptoms: isYes, wasSymptomaticAtTimeOfCall: null },
        };

        const result = assumedWasSymptomaticAtTimeOfCall(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(true);
    });

    it('should return true if wasSymptomaticAtTimeOfCall was assumed because wasSymptomaticAtTimeOfCall is unknown', () => {
        const caseFragments = {
            symptoms: { hasSymptoms: isYes, wasSymptomaticAtTimeOfCall: isUnknown },
        };

        const result = assumedWasSymptomaticAtTimeOfCall(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(true);
    });

    it('should return false if wasSymptomaticAtTimeOfCall was not assumed because there are no symptoms', () => {
        const caseFragments = { symptoms: {} };

        const result = assumedWasSymptomaticAtTimeOfCall(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(false);
    });

    it('should return false if wasSymptomaticAtTimeOfCall was not assumed because wasSymptomaticAtTimeOfCall is not unknown', () => {
        const caseFragments = {
            symptoms: { hasSymptoms: isYes, wasSymptomaticAtTimeOfCall: isNo },
        };

        const result = assumedWasSymptomaticAtTimeOfCall(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(false);
    });
});

describe('assumedStillHadSymptomsAt', () => {
    it('should return true if stillHadSymptomsAt was assumed because stillHadSymptomsAt is not set', () => {
        const caseFragments = { symptoms: { hasSymptoms: isYes, wasSymptomaticAtTimeOfCall: null } };

        const result = assumedStillHadSymptomsAt(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(true);
    });

    it('should return false if stillHadSymptomsAt was not assumed because there are no symptoms', () => {
        const caseFragments = { symptoms: {} };

        const result = assumedStillHadSymptomsAt(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(false);
    });

    it('should return false if stillHadSymptomsAt was not assumed because stillHadSymptomsAt is set', () => {
        const caseFragments = {
            symptoms: { hasSymptoms: isYes, wasSymptomaticAtTimeOfCall: isYes, stillHadSymptomsAt: '2021-09-04' },
        };

        const result = assumedStillHadSymptomsAt(caseFragments as CovidCaseUnionDTO);

        expect(result).toBe(false);
    });
});

describe('getTaskLastContactDateWarning', () => {
    const taskBaseValues: Task = {
        uuid: '1234',
        caseUuid: '5678',
        informStatus: InformStatusV1.VALUE_uninformed,
        internalReference: '',
        isSource: false,
        source: 'portal',
        status: TaskStatus.Open,
        taskType: 'contact',
    };

    it.each([
        {
            description: 'Should return undefined when no dateOfLastExposure',
            expected: undefined,
            task: taskBaseValues,
            group: TaskGroup.Contact,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isNo,
                },
                hospital: { isAdmitted: isNo, reason: null },
                immunity: { isImmune: null },
                test: {
                    dateOfTest: '2021-04-02',
                },
            },
        },
        {
            description: 'Should return undefined for contact group',
            expected: undefined,
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-04-02',
            },
            group: TaskGroup.Contact,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isNo,
                },
                hospital: { isAdmitted: isNo, reason: null },
                immunity: { isImmune: null },
                test: {
                    dateOfTest: '2021-04-02',
                },
            },
        },
        {
            description: 'Should return undefined for contact group when infectiousDates = null',
            expected: undefined,
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-04-02',
            },
            group: TaskGroup.Contact,
            // no case fragments = null infectiousDates
            caseFragments: {},
        },
        {
            description: 'Should return warning message for contact group',
            expected: 'Het laatste contact was niet in de besmettelijke periode. Controleer de laatste contactdatum.',
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-04-01',
            },
            group: TaskGroup.Contact,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isNo,
                },
                hospital: { isAdmitted: isNo, reason: null },
                immunity: { isImmune: null },
                test: {
                    dateOfTest: '2021-04-02',
                },
            },
        },
        {
            description: 'Should return undefined for positive source',
            expected: undefined,
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-03-18',
            },
            group: TaskGroup.PositiveSource,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                test: {
                    dateOfSymptomOnset: '2021-04-01',
                },
            },
        },
        {
            description: 'Should return undefined for positive source when sourceDates = null',
            expected: undefined,
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-03-18',
            },
            group: TaskGroup.PositiveSource,
            // no case fragments = null sourceDates
            caseFragments: {},
        },
        {
            description: 'Should return warning message for positive source',
            expected: 'Het laatste contact was niet in de bronperiode. Weet je zeker dat dit een broncontact is?',
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-03-17',
            },
            group: TaskGroup.PositiveSource,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                test: {
                    dateOfSymptomOnset: '2021-04-01',
                },
            },
        },
        {
            description: 'Should return undefined for symptomatic source',
            expected: undefined,
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-03-18',
            },
            group: TaskGroup.SymptomaticSource,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                test: {
                    dateOfSymptomOnset: '2021-04-01',
                },
            },
        },
        {
            description: 'Should return warning message for symptomatic source',
            expected: 'Het laatste contact was niet in de bronperiode. Weet je zeker dat dit een broncontact is?',
            task: {
                ...taskBaseValues,
                dateOfLastExposure: '2021-03-17',
            },
            group: TaskGroup.SymptomaticSource,
            caseFragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                test: {
                    dateOfSymptomOnset: '2021-04-01',
                },
            },
        },
    ])('$description', ({ expected, task, group, caseFragments }) => {
        const warning = getTaskLastContactDateWarning(task, group, caseFragments as CovidCaseUnionDTO);
        expect(warning).toBe(expected);
    });
});

describe('canDetermineInfectiousPeriod', () => {
    it('should return false when hasSymptoms is unknown', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_unknown, null, null)).toBeFalsy();
    });
    it('should return false when hasSymptoms is no and dateOfSymptomOnset is not provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_no, null, null)).toBeFalsy();
    });
    it('should return false when hasSymptoms is true and dateOfTest is not provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_yes, null, null)).toBeFalsy();
    });
    it('should return true when hasSymptoms is true and dateOfTest is provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_yes, '2023-11-01', null)).toBeTruthy();
    });
    it('should return true when hasSymptoms is false and dateOfSymptomOnset is provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_no, null, '2023-11-01')).toBeTruthy();
    });
    it('should return true when hasSymptoms is false and dateOfSymptomOnset and dateOfTest are provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_no, '2023-11-01', '2023-11-01')).toBeTruthy();
    });
    it('should return true when hasSymptoms is true and dateOfSymptomOnset and dateOfTest are provided', () => {
        expect(canDetermineInfectiousPeriod(YesNoUnknownV1.VALUE_yes, '2023-11-01', '2023-11-01')).toBeTruthy();
    });
});
