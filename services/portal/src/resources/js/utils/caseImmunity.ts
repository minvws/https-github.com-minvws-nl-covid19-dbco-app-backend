import { isYes } from '@/components/form/ts/formOptions';
import { VaccineV1 } from '@dbco/enum';
import { isAfter } from 'date-fns';
import { getDifferenceInDays } from './date';
import type { GeneralCommonDTO } from '@dbco/schema/task/general/generalCommon';
import type { VaccineInjectionCommon } from '@dbco/schema/shared/vaccineInjection/vaccineInjectionCommon';
import type { DTO } from '@dbco/schema/dto';
import { StoreType } from '@/store/storeType';
import type { CovidCaseUnionDTO, TaskUnionDTO } from '@dbco/schema/unions';

type FragmentType = CovidCaseUnionDTO | TaskUnionDTO;

// Define vaccine categories
const vaccinesMrna = [VaccineV1.VALUE_pfizer, VaccineV1.VALUE_moderna];
const vaccinesSingle = [VaccineV1.VALUE_janssen];

/**
 * Typeguard to tell TypeScript that the general fragment is of the CommonDTO type of the task general fragment
 * @param _general the general fragment to return the type for
 * @param storeType
 * @returns
 */
export const isTaskGeneral = (_general: any, storeType: StoreType): _general is GeneralCommonDTO =>
    storeType === StoreType.TASK;

export const isPotentiallyVaccinated = (caseFragments: FragmentType, storeType: StoreType): boolean => {
    if (!caseFragments.vaccination?.isVaccinated) return false;

    const { general, test, vaccination } = caseFragments;
    const { isVaccinated, vaccineInjections } = vaccination;

    const comparisonDate = isTaskGeneral(general, storeType) ? general.dateOfLastExposure : test.dateOfTest;

    if (isVaccinated === isYes) {
        // If not enough information, return false (=X)
        if (!comparisonDate) return false;

        // If no vaccines, return false (=X)
        if (!vaccineInjections || vaccineInjections.length === 0) return false;

        // getValidVaccines ignores any vaccinations less than a week ago
        const validVaccines = getValidVaccines(vaccineInjections, comparisonDate);
        const vaccineCounts = getVaccineCounts(validVaccines);

        const countTotalVaccines = validVaccines.length;
        const countMrnaVaccines = countVaccineTypes(vaccineCounts, vaccinesMrna);
        const countSingleVaccines = countVaccineTypes(vaccineCounts, vaccinesSingle);
        const countUnknownVaccines = countVaccineTypes(vaccineCounts, [VaccineV1.VALUE_unknown]);

        // If at least 3 injections of which one is mrna/unknown, return true (=?)
        if (countTotalVaccines >= 3 && (countMrnaVaccines > 0 || countUnknownVaccines > 0)) {
            return true;
        }

        if (countTotalVaccines >= 2) {
            /*
             * If at least 2 injections of which 1 is single/unknown + 1 is mrna/unknown, return true (=?)
             * Possible combinations:
             * SINGLE + MRNA
             * SINGLE + UNKNOWN
             * UNKNOWN + MRNA
             * UNKNOWN + UNKNOWN
             */
            if (countSingleVaccines >= 1 && countMrnaVaccines >= 1) return true;
            if (countSingleVaccines >= 1 && countUnknownVaccines >= 1) return true;
            if (countUnknownVaccines >= 1 && countMrnaVaccines >= 1) return true;
            if (countUnknownVaccines >= 2) return true;
        }
    }

    // In all other cases, return false (=X)
    return false;
};

export const isPreviouslyInfectedAndPotentiallyVaccinated = (
    caseFragments: FragmentType,
    storeType: StoreType
): boolean => {
    if (!caseFragments.test?.isReinfection) return false;
    if (!caseFragments.vaccination?.isVaccinated) return false;

    const { general, test, vaccination } = caseFragments;
    const { isReinfection, dateOfTest } = test;
    const { isVaccinated, vaccineInjections } = vaccination;

    const comparisonDate = isTaskGeneral(general, storeType) ? general.dateOfLastExposure : dateOfTest;

    // If infected earlier...
    if (isReinfection === isYes && isVaccinated === isYes) {
        // If not enough information, return false (=X)
        if (!comparisonDate) return false;

        // If no vaccines, return false (=X)
        if (!vaccineInjections || vaccineInjections.length === 0) return false;

        // getValidVaccines ignores any vaccinations less than a week ago
        const validVaccines = getValidVaccines(vaccineInjections, comparisonDate);
        const vaccineCounts = getVaccineCounts(validVaccines);

        const countTotalVaccines = validVaccines.length;
        const countMrnaVaccines = countVaccineTypes(vaccineCounts, vaccinesMrna);
        const countUnknownVaccines = countVaccineTypes(vaccineCounts, [VaccineV1.VALUE_unknown]);

        // ... and at least 2 injections of which one is mrna/unknown, return true (=?)
        if (countTotalVaccines >= 2 && (countMrnaVaccines > 0 || countUnknownVaccines > 0)) return true;
    }

    // In all other cases, return false (=X)
    return false;
};

export const isRecentlyInfected = (caseFragments: FragmentType, storeType: StoreType): boolean => {
    if (!caseFragments.test?.isReinfection) return false;

    const { general, test } = caseFragments;
    const { isReinfection, dateOfTest, previousInfectionDateOfSymptom } = test;

    const comparisonDate = isTaskGeneral(general, storeType) ? general.dateOfLastExposure : dateOfTest;

    if (isReinfection === isYes && previousInfectionDateOfSymptom) {
        if (!comparisonDate) return false;

        const daysSinceFirstInfection = getDifferenceInDays(
            new Date(comparisonDate),
            new Date(previousInfectionDateOfSymptom)
        );

        // is after 01-01-2022
        if (isAfter(new Date(1, 1, 2022), new Date(previousInfectionDateOfSymptom))) {
            return true;
        }

        // is less than 8 weeks ago
        if (daysSinceFirstInfection <= 56) {
            return true;
        }
    }

    return false;
};

const getValidVaccines = (injections: DTO<VaccineInjectionCommon[]>, comparisonDate: string) => {
    const validInjections = injections.filter((injection) => {
        // include empty dates as potential valid vaccine - cant give a definate no
        if (!injection.injectionDate) {
            return true;
        }
        // injections with a date in the last week are not accepted
        return getDifferenceInDays(new Date(comparisonDate), new Date(injection.injectionDate)) >= 7;
    });
    return validInjections;
};

const getVaccineCounts = (validVaccines: DTO<VaccineInjectionCommon>[]) =>
    validVaccines.reduce((totals: Record<string, number>, injection) => {
        if (!injection.vaccineType) return totals;

        totals[injection.vaccineType] ? totals[injection.vaccineType]++ : (totals[injection.vaccineType] = 1);

        return totals;
    }, {});

const countVaccineTypes = (vaccineCounts: Record<string, number>, selection: string[]): number =>
    Object.entries(vaccineCounts).reduce((total: number, [vaccine, count]) => {
        // If this vaccine is in the selection, add to total
        if (selection.includes(vaccine)) total += count;
        return total;
    }, 0);
