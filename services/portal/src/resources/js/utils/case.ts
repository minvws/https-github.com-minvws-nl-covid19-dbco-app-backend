import { isNo, isUnknown, isYes } from '@/components/form/ts/formOptions';
import type { YesNoUnknownV1 } from '@dbco/enum';
import { HospitalReasonV1 } from '@dbco/enum';
import type { Task } from '@dbco/portal-api/task.dto';
import { TaskGroup } from '@dbco/portal-api/task.dto';
import type { SymptomsV1 } from '@dbco/schema/covidCase/symptoms/symptomsV1';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { addDays, isAfter, isBefore, subDays } from 'date-fns';
import { formatDateLong, isBetweenDays, parseDate } from './date';
import type { Range } from './caseDateRanges';

const hasSymptomsV1 = (symptoms: CovidCaseUnionDTO['symptoms']): symptoms is SymptomsV1 =>
    symptoms.hasOwnProperty('wasSymptomaticAtTimeOfCall');

/**
 * Calculate the isolation day based on input given in fragments
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {Date|null}
 */
export const determineIsolationDay = (caseFragments: Partial<CovidCaseUnionDTO>): Date | null => {
    if (isMedicalPeriodInfoIncomplete(caseFragments)) return null;

    if (isHospitalizedForCovid(caseFragments)) {
        if (!caseFragments.test?.dateOfSymptomOnset) return null;
        return addDays(new Date(caseFragments.test.dateOfSymptomOnset), 10);
    }

    // If symptomatic
    if (isSymptomatic(caseFragments)) {
        if (!caseFragments.test?.dateOfSymptomOnset) return null;
        const minimum = addDays(new Date(caseFragments.test.dateOfSymptomOnset), 5);
        const maximum = addDays(new Date(caseFragments.test.dateOfSymptomOnset), 10);

        if (caseFragments.symptoms && hasSymptomsV1(caseFragments.symptoms)) {
            // When stillHadSymptomsAt is not known, return the minimum date
            if (!caseFragments.symptoms?.stillHadSymptomsAt) return minimum;

            const proposal = addDays(new Date(caseFragments.symptoms.stillHadSymptomsAt), 1);

            // Only use the proposal when within the minimum and maximum of 5-10 days
            if (isAfter(proposal, maximum)) return maximum;
            if (isBefore(proposal, minimum)) return minimum;

            return proposal;
        }

        return minimum;
    }

    if (!caseFragments.test?.dateOfTest) return null;

    // If asymptomatic
    return addDays(new Date(caseFragments.test.dateOfTest), 5);
};

export const getIsolationAdviceSymptomatic = (dateOfSymptomOnset: Date): string => {
    const sixDaysFromSymptomsOnSet = formatDateLong(addDays(dateOfSymptomOnset, 6));
    const tenDaysFromSymptomsOnset = formatDateLong(addDays(dateOfSymptomOnset, 10));
    return `Isolatie symptomatische index: thuis blijven tot en met ${tenDaysFromSymptomsOnset} Index mag eventueel vanaf ${sixDaysFromSymptomsOnSet} naar buiten, als de index 24 uur klachtenvrij is en niet in het ziekenhuis is opgenomen.`;
};

/**
 * Get start_date and end_date for source period.
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {SourceDates|null}
 */
export const sourceDates = (caseFragments: CovidCaseUnionDTO): Range | null => {
    const dateOfSymptomOnset = caseFragments.test?.dateOfSymptomOnset;
    if (!dateOfSymptomOnset) return null;

    return {
        startDate: subDays(new Date(dateOfSymptomOnset), 14),
        endDate: isSymptomatic(caseFragments)
            ? subDays(new Date(dateOfSymptomOnset), 2)
            : subDays(new Date(dateOfSymptomOnset), 1),
    };
};

/**
 * Get start_date and end_date for infectious period.
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {InfectiousDates|null} When not known, null is returned
 */
export const infectiousDates = (caseFragments: CovidCaseUnionDTO): Range | null => {
    const isolationDay = determineIsolationDay(caseFragments);
    if (!isolationDay) return null;

    let startDate: Date;
    if (isSymptomatic(caseFragments)) {
        if (!caseFragments.test.dateOfSymptomOnset) return null;
        startDate = subDays(new Date(caseFragments.test.dateOfSymptomOnset), 2);
    } else {
        if (!caseFragments.test.dateOfTest) return null;
        startDate = new Date(caseFragments.test.dateOfTest);
    }
    return {
        startDate,
        endDate: isolationDay,
    };
};

/**
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {boolean|null} When not known, null is returned
 */
export const isSymptomatic = (caseFragments: Partial<CovidCaseUnionDTO>): boolean | null => {
    if (!caseFragments?.symptoms?.hasSymptoms || caseFragments.symptoms.hasSymptoms === isUnknown) return null;

    return caseFragments.symptoms.hasSymptoms === isYes;
};

/**
 *
 * @param {YesNoUnknownV1|null|undefined} hasSymptoms
 * @param {string|null} dateOfSymptomOnset
 * @param {Date|null} dateOfTest
 * @returns {boolean}
 */
export const canDetermineInfectiousPeriod = (
    hasSymptoms: YesNoUnknownV1 | null | undefined,
    dateOfSymptomOnset: string | null,
    dateOfTest: string | null
) => {
    if (!hasSymptoms || hasSymptoms === isUnknown) return false;
    if (hasSymptoms === isYes && dateOfSymptomOnset === null) return false;
    if (hasSymptoms === isNo && dateOfTest === null) return false;

    return true;
};

/**
 *
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {boolean}
 */
export const isMedicalPeriodInfoIncomplete = (caseFragments: Partial<CovidCaseUnionDTO>): boolean => {
    // Special case for there being no caseFragments
    if (!caseFragments) return true;

    // Hospitalized for Covid track
    // If hospital.isAdmitted is true, hospital.reason is covid and dateofSymptomOnset is null or undefined return false.
    if (
        caseFragments.hospital?.isAdmitted === isYes &&
        caseFragments.hospital?.reason === HospitalReasonV1.VALUE_covid &&
        !caseFragments.test?.dateOfSymptomOnset
    )
        return true;

    // If hospital.isAdmitted is true, hospital.reason is covid and dateofSymptomOnset is defined return true.
    if (
        caseFragments.hospital?.isAdmitted === isYes &&
        caseFragments.hospital?.reason === HospitalReasonV1.VALUE_covid &&
        !!caseFragments.test?.dateOfSymptomOnset
    )
        return false;

    // Not hospitalized for Covid track
    if (!caseFragments.symptoms?.hasSymptoms || caseFragments.symptoms?.hasSymptoms === isUnknown) return true;

    // If symptomatic
    if (caseFragments.symptoms?.hasSymptoms === isYes && !caseFragments.test?.dateOfSymptomOnset) return true;

    // If asymptomatic and dateOfTest null or undefined
    if (caseFragments.symptoms?.hasSymptoms === isNo && !caseFragments.test?.dateOfTest) return true;

    return false;
};

/**
 *
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {boolean}
 */
export const isMedicalPeriodInfoNotDefinitive = (caseFragments: CovidCaseUnionDTO): boolean => {
    // Special case for there being no caseFragments
    if (!caseFragments) return true;

    // IF any of the assumptions are applicable, then return true
    if (
        assumptionHospitalIsAdmittedUnknown(caseFragments) ||
        assumptionHospitalReasonUnknown(caseFragments) ||
        assumedWasSymptomaticAtTimeOfCall(caseFragments) ||
        assumedStillHadSymptomsAt(caseFragments)
    )
        return true;
    return false;
};

// Assumptions
export const assumptionHospitalIsAdmittedUnknown = (caseFragments: CovidCaseUnionDTO): boolean => {
    // Hospital admitted = null/undefined/unknown ==> return true
    return !caseFragments?.hospital?.isAdmitted || caseFragments?.hospital?.isAdmitted === isUnknown;
};

export const assumptionHospitalReasonUnknown = (caseFragments: CovidCaseUnionDTO): boolean => {
    // admitted = ja AND reason = null/undefined/unknown ==> return true
    return (
        caseFragments?.hospital?.isAdmitted === isYes &&
        (!caseFragments?.hospital?.reason || caseFragments?.hospital?.reason === HospitalReasonV1.VALUE_unknown)
    );
};

/**
 * returns true if the value of wasSymptomaticAtTimeOfCall will be assumed for determining the isolationDay
 * @param caseFragments
 * @returns
 */
export const assumedWasSymptomaticAtTimeOfCall = (caseFragments: CovidCaseUnionDTO): boolean =>
    hasSymptomsV1(caseFragments?.symptoms) &&
    caseFragments?.symptoms?.hasSymptoms === isYes &&
    (!caseFragments?.symptoms?.wasSymptomaticAtTimeOfCall ||
        caseFragments?.symptoms?.wasSymptomaticAtTimeOfCall === isUnknown);

/**
 * returns true if the value of stillHadSymptomsAt will be assumed for determining the isolationDay
 * @param caseFragments
 * @returns
 */
export const assumedStillHadSymptomsAt = (caseFragments: CovidCaseUnionDTO): boolean =>
    hasSymptomsV1(caseFragments?.symptoms) &&
    caseFragments?.symptoms?.hasSymptoms === isYes &&
    !caseFragments?.symptoms?.stillHadSymptomsAt;

/**
 * @param {CovidCaseUnionDTO} caseFragments
 * @returns {boolean}
 */
export const isHospitalizedForCovid = (caseFragments: Partial<CovidCaseUnionDTO>): boolean => {
    // Only "true" if isHospitalized is "yes", null should be assumed "false"!
    const isHospitalized = caseFragments?.hospital?.isAdmitted === isYes;

    // IF covid or null/undefined/onbekend assume the case had Covid19
    const isReasonCovid =
        !caseFragments?.hospital?.reason ||
        caseFragments?.hospital?.reason === HospitalReasonV1.VALUE_covid ||
        caseFragments?.hospital?.reason === HospitalReasonV1.VALUE_unknown;
    if (isHospitalized && isReasonCovid) return true;

    return false;
};

export const getTaskLastContactDateWarning = (
    task: Task,
    group: TaskGroup,
    caseFragments: CovidCaseUnionDTO
): string | undefined => {
    if (!task.dateOfLastExposure) return;

    const dateOfLastExposure = parseDate(task.dateOfLastExposure, 'yyyy-MM-dd');

    // Contact
    if (group === TaskGroup.Contact) {
        const datesInfectious = infectiousDates(caseFragments);
        if (!datesInfectious) return;

        if (isBetweenDays(dateOfLastExposure, datesInfectious.startDate, datesInfectious.endDate, '[]')) return;

        return 'Het laatste contact was niet in de besmettelijke periode. Controleer de laatste contactdatum.';
    }

    // Source
    const datesInfectious = sourceDates(caseFragments);
    if (!datesInfectious) return;

    if (isBetweenDays(dateOfLastExposure, datesInfectious.startDate, datesInfectious.endDate, '[]')) return;

    return 'Het laatste contact was niet in de bronperiode. Weet je zeker dat dit een broncontact is?';
};
