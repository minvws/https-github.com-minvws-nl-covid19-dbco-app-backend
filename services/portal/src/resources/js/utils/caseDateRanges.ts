import { CaseFilterKey } from '@/components/form/ts/formTypes';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { subDays } from 'date-fns';
import { determineIsolationDay, isSymptomatic } from './case';

export interface Range {
    startDate: Date;
    endDate: Date;
    key?: CaseFilterKey;
}

/**
 * @argument filterKeys CaseFilterKey[]
 */
export const caseDateRanges = (caseFragments: Partial<CovidCaseUnionDTO>, filterKeys?: CaseFilterKey[]) => {
    /**
     * The order of the ranges determines the order in the legend
     */
    const ranges: Range[] = [];
    if (!filterKeys?.length || filterKeys.includes(CaseFilterKey.Source)) addSourceRange(ranges, caseFragments);
    if (!filterKeys?.length || filterKeys.includes(CaseFilterKey.InfectiousPeriod))
        addInfectiousRange(ranges, caseFragments);
    if (!filterKeys?.length || filterKeys.includes(CaseFilterKey.SymptomOnset))
        addSymptomOnsetRange(ranges, caseFragments);
    if (!filterKeys?.length || filterKeys.includes(CaseFilterKey.TestDate)) addTestRange(ranges, caseFragments);
    return ranges;
};

export const addSourceRange = (
    ranges: Range[],
    caseFragments: Partial<CovidCaseUnionDTO>,
    overrides: Partial<Range> = {}
) => {
    const dateOfSymptomOnset = caseFragments.test?.dateOfSymptomOnset;
    if (!dateOfSymptomOnset) return;

    const startSource = subDays(new Date(dateOfSymptomOnset), 14);
    const endSource = isSymptomatic(caseFragments)
        ? subDays(new Date(dateOfSymptomOnset), 2)
        : subDays(new Date(dateOfSymptomOnset), 1);

    ranges.push({
        startDate: startSource,
        endDate: endSource,
        key: CaseFilterKey.Source,
        ...overrides,
    });
};

export const addInfectiousRange = (
    ranges: Range[],
    caseFragments: Partial<CovidCaseUnionDTO>,
    overrides: Partial<Range> = {}
) => {
    const isolationDay = determineIsolationDay(caseFragments);
    if (!isolationDay) return;

    let startDate: Date;
    if (isSymptomatic(caseFragments)) {
        if (!caseFragments.test?.dateOfSymptomOnset) return;
        startDate = subDays(new Date(caseFragments.test.dateOfSymptomOnset), 2);
    } else {
        if (!caseFragments.test?.dateOfTest) return;
        startDate = new Date(caseFragments.test.dateOfTest);
    }

    ranges.push({
        startDate,
        endDate: isolationDay,
        key: CaseFilterKey.InfectiousPeriod,
        ...overrides,
    });
};

const addSymptomOnsetRange = (
    ranges: Range[],
    caseFragments: Partial<CovidCaseUnionDTO>,
    overrides: Partial<Range> = {}
) => {
    const dateOfSymptomOnset = caseFragments.test?.dateOfSymptomOnset;
    if (!dateOfSymptomOnset || !isSymptomatic(caseFragments)) return;

    const dateObject = new Date(dateOfSymptomOnset);
    ranges.push({
        startDate: dateObject,
        endDate: dateObject,
        key: CaseFilterKey.SymptomOnset,
        ...overrides,
    });
};

const addTestRange = (ranges: Range[], caseFragments: Partial<CovidCaseUnionDTO>, overrides: Partial<Range> = {}) => {
    const dateOfTest = caseFragments.test?.dateOfTest;
    if (!dateOfTest) return;

    const dateObject = new Date(dateOfTest);
    ranges.push({
        startDate: dateObject,
        endDate: dateObject,
        key: CaseFilterKey.TestDate,
        ...overrides,
    });
};
