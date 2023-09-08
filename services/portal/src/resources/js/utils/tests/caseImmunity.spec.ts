import { subDays } from 'date-fns';

import { isYes, isNo } from '@/components/form/ts/formOptions';
import {
    isPotentiallyVaccinated,
    isPreviouslyInfectedAndPotentiallyVaccinated,
    isRecentlyInfected,
} from '../caseImmunity';
import { VaccineV1 } from '@dbco/enum';
import type { CovidCaseV1DTO } from '@dbco/schema/covidCase/covidCaseV1';
import { StoreType } from '@/store/storeType';

describe('case - isPotentiallyVaccinated', () => {
    it.each([
        {
            testDescription: 'index not vaccinated (undefined)',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: undefined,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task not vaccinated (undefined)',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: undefined,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index not vaccinated (isNo)',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: isNo,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task not vaccinated (isNo)',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: isNo,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: not enough vaccines',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: not enough vaccines',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 vaccines - date for last vaccine too close',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 6),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 vaccines - date for last vaccine too close',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 6),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines without dates',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines without dates',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        // excepted vaccine combination tests for 3 injections
        {
            testDescription: 'index: 3 accepted vaccines - pfizer + pfizer + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines - pfizer + pfizer + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines - moderna + moderna + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines - moderna + moderna + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines - unknown + unknown + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines - unknown + unknown + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines mix - curevac + gsk + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines mix - curevac + gsk + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines mix - astrazeneca + other + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_other,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines mix - astrazeneca + other + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_other,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 accepted vaccines mix - astrazeneca + astrazeneca + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 accepted vaccines mix - astrazeneca + astrazeneca + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        // none excepted vaccine combination tests for 3 injections
        {
            testDescription: 'index: 3 none accepted vaccines mix - astrazeneca + astrazeneca + astrazeneca',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 none accepted vaccines mix - astrazeneca + astrazeneca + astrazeneca',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 3 none accepted vaccines mix - curevac + gsk + other',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_other,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 3 none accepted vaccines mix - curevac + gsk + other',
            expectedResult: false,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
                {
                    injectionDate: subDays(new Date(), 112),
                    vaccineType: VaccineV1.VALUE_other,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        // excepted vaccine combination tests for 2 injections
        {
            testDescription: 'index: 2 accepted vaccines mix - jansen + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - jansen + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines mix - jansen + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - jansen + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines mix - jansen + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - jansen + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines mix - unknown + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - unknown + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines mix - unknown + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - unknown + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines mix - unknown + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines mix - unknown + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
    ])(
        '[%#] "%j"',
        ({ expectedResult, storeType, isVaccinated, vaccineInjections, dateOfTest, dateOfLastExposure }) => {
            const covidCase = {
                general: {
                    dateOfLastExposure,
                },
                test: {
                    dateOfTest,
                },
                vaccination: {
                    isVaccinated,
                    vaccineInjections,
                },
            } as unknown as CovidCaseV1DTO;

            const result = isPotentiallyVaccinated(covidCase, storeType);

            expect(result).toEqual(expectedResult);
        }
    );
});

describe('case - isPreviouslyInfectedAndPotentiallyVaccinated', () => {
    // True requires a moderna, pfizer or unknown vaccine as of 3637 - potential booster vaccine
    it.each([
        {
            testDescription: 'index: not reinfection - undefined',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: undefined,
            isVaccinated: isYes,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task: not reinfection - undefined',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: undefined,
            isVaccinated: isYes,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: not reinfection - isNo',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isNo,
            isVaccinated: isYes,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task: not reinfection - isNo',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isNo,
            isVaccinated: isYes,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: not vaccinated - undefined',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: undefined,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task: not vaccinated - undefined',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: undefined,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: not vaccinated - isNo',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isNo,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'task: not vaccinated - isNo',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isNo,
            vaccineInjections: [],
            dateOfTest: new Date(),
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: not enough vaccines',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: not enough vaccines',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: date for second vaccine too close',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 6),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: date for second vaccine too close',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 6),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 accepted vaccines without dates',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 accepted vaccines without dates',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        // excepted vaccine combination tests for 2 injections
        {
            testDescription: 'index: accepted vaccines - pfizer + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines - pfizer + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: accepted vaccines - moderna + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines - moderna + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: accepted vaccines - unknown + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines - unknown + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: accepted vaccines mix - curevac + pfizer',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines mix - curevac + pfizer',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_pfizer,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: accepted vaccines mix - astrazeneca + moderna',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines mix - astrazeneca + moderna',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_moderna,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: accepted vaccines mix - gsk + unknown',
            expectedResult: true,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: accepted vaccines mix - gsk + unknown',
            expectedResult: true,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_unknown,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        // none excepted vaccine combination tests for 2 injections
        {
            testDescription: 'index: 2 non accepted vaccines mix - jansen + curevac',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 non accepted vaccines mix - jansen + curevac',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_janssen,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_curevac,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 non accepted vaccines mix - astrazeneca + gsk',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 non accepted vaccines mix - astrazeneca + gsk',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_gsk,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
        {
            testDescription: 'index: 2 non accepted vaccines mix - astrazeneca + other',
            expectedResult: false,
            storeType: StoreType.INDEX,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_other,
                },
            ],
            dateOfTest: new Date(),
            dateOfLastExposure: null,
        },
        {
            testDescription: 'task: 2 non accepted vaccines mix - astrazeneca + other',
            expectedResult: false,
            storeType: StoreType.TASK,
            isReinfection: isYes,
            isVaccinated: isYes,
            vaccineInjections: [
                {
                    injectionDate: subDays(new Date(), 8),
                    vaccineType: VaccineV1.VALUE_astrazeneca,
                },
                {
                    injectionDate: subDays(new Date(), 56),
                    vaccineType: VaccineV1.VALUE_other,
                },
            ],
            dateOfTest: null,
            dateOfLastExposure: new Date(),
        },
    ])(
        '[%#] "%j"',
        ({
            expectedResult,
            storeType,
            isReinfection,
            isVaccinated,
            vaccineInjections,
            dateOfTest,
            dateOfLastExposure,
        }) => {
            const covidCase = {
                general: {
                    dateOfLastExposure,
                },
                test: {
                    isReinfection,
                    dateOfTest,
                },
                vaccination: {
                    isVaccinated,
                    vaccineInjections,
                },
            } as unknown as CovidCaseV1DTO;

            const result = isPreviouslyInfectedAndPotentiallyVaccinated(covidCase, storeType as StoreType);

            expect(result).toEqual(expectedResult);
        }
    );
});

describe('case - isRecentlyInfected', () => {
    it.each([
        ['index: not reinfection', false, StoreType.INDEX, undefined, new Date(), new Date(), new Date()],
        ['task: not reinfection', false, StoreType.TASK, undefined, new Date(), new Date(), new Date()],
        ['index: not reinfection', false, StoreType.INDEX, isNo, new Date(), new Date(), new Date()],
        ['task: not reinfection', false, StoreType.TASK, isNo, new Date(), new Date(), new Date()],
        ['index: less than 8 weeks ago', true, StoreType.INDEX, isYes, subDays(new Date(), 56), new Date(), null],
        ['index: more than 8 weeks ago', false, StoreType.INDEX, isYes, subDays(new Date(), 57), new Date(), null],
        ['task: less than 8 weeks ago', true, StoreType.TASK, isYes, subDays(new Date(), 56), null, new Date()],
        ['task: more than 8 weeks ago', false, StoreType.TASK, isYes, subDays(new Date(), 57), null, new Date()],
        ['index: before 01-01-2022', false, StoreType.INDEX, isYes, new Date('31-12-2021'), null, new Date()],
        ['task: before 01-01-2022', false, StoreType.TASK, isYes, new Date('31-12-2021'), null, new Date()],
    ])(
        '[%#] "%s": expect "%s" for storeType: "%s", isReinfection: "%s", previousInfectionDateOfSymptom: "%s", dateOfTest: "%s", dateOfLastExposure: "%s"',
        (
            testDescription,
            expectedResult,
            storeType,
            isReinfection,
            previousInfectionDateOfSymptom,
            dateOfTest,
            dateOfLastExposure
        ) => {
            const covidCase = {
                general: {
                    dateOfLastExposure,
                },
                test: {
                    isReinfection,
                    dateOfTest,
                    previousInfectionDateOfSymptom,
                },
            } as unknown as CovidCaseV1DTO;

            const result = isRecentlyInfected(covidCase, storeType);

            expect(result).toEqual(expectedResult);
        }
    );
});
