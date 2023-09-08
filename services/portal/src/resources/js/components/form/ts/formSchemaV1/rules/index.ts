import { isSymptomatic } from '@/utils/case';
import type { DTO } from '@dbco/schema/dto';
import type { AllowedVersions } from '..';
import { formatDate } from '@/utils/date';
import type { ExtensiveContactTracingReasonV1, YesNoUnknownV1 } from '@dbco/enum';
import { isNo, isYes } from '../../formOptions';
import type { SchemaRule } from '../../schemaType';
import type { CovidCaseV1 } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2 } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3 } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4 } from '@dbco/schema/covidCase/covidCaseV4';
import type { CovidCaseV5 } from '@dbco/schema/covidCase/covidCaseV5';
import type { CovidCaseV6 } from '@dbco/schema/covidCase/covidCaseV6';

export const dateOfSymptomOnset: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'DateSymptomOnset',
    watch: 'test.dateOfSymptomOnset',
    callback: (data) => updateInfectiousnessDate(data),
};

export const dateOfTestAndSymptoms: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'DateOfTest + Symptoms',
    watch: ['test.dateOfTest', 'symptoms.symptoms'],
    callback: (data) => ({
        ...updateInfectiousnessDate(data),
        ...updateDateOfSymptomOnset(data),
    }),
};

export const hasSymptoms: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'HasSymptoms',
    watch: 'symptoms.hasSymptoms',
    callback: (data, newVal: any, [oldHasSymptoms]: [YesNoUnknownV1]) => {
        let changes: any = {};

        if (oldHasSymptoms === isNo) {
            // If the dateOfSymptomOnset was previously auto-filled, clear it
            changes['test.dateOfSymptomOnset'] = null;
        } else {
            changes = { ...changes, ...updateDateOfSymptomOnset(data) };
        }
        changes = { ...changes, ...updateInfectiousnessDate(data) };

        return changes;
    },
};

const isExtensiveContactTracing: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'IsExtensiveContactTracing',
    watch: 'extensiveContactTracing.reasons',
    callback: (data, [newReasons]: [ExtensiveContactTracingReasonV1[]]) => {
        if (!data.extensiveContactTracing.receivesExtensiveContactTracing && newReasons?.length > 0) {
            return {
                'extensiveContactTracing.receivesExtensiveContactTracing': isYes,
            };
        }

        return {};
    },
};

export const hasSymptomsButNoWasSymptomaticAtTimeOfCall: SchemaRule<
    DTO<CovidCaseV1 | CovidCaseV2 | CovidCaseV3 | CovidCaseV4 | CovidCaseV5 | CovidCaseV6>
> = {
    title: 'HasSymptomsButNoWasSymptomaticAtTimeOfCall',
    watch: 'symptoms.hasSymptoms',
    callback: (data, [hasSymptoms]: [YesNoUnknownV1]) => {
        // If the index has symptoms, but wasSymptomaticAtTimeOfCall was not answered yet, set stillHadSymptomsAt to today
        if (hasSymptoms === isYes && !data.symptoms.wasSymptomaticAtTimeOfCall) {
            return { 'symptoms.stillHadSymptomsAt': formatDate(Date.now(), 'yyyy-MM-dd') };
        }

        return {};
    },
};

export const isStillSymptomatic: SchemaRule<DTO<AllowedVersions['index']>> = {
    title: 'IsStillSymptomatic',
    watch: 'symptoms.wasSymptomaticAtTimeOfCall',
    callback: (data, [wasSymptomaticAtTimeOfCall]: [YesNoUnknownV1]) => ({
        'symptoms.stillHadSymptomsAt':
            wasSymptomaticAtTimeOfCall === isNo ? null : formatDate(Date.now(), 'yyyy-MM-dd'),
    }),
};

const indexRules: SchemaRule<DTO<AllowedVersions['index']>>[] = [
    dateOfSymptomOnset,
    dateOfTestAndSymptoms,
    hasSymptoms,
    isExtensiveContactTracing,
    hasSymptomsButNoWasSymptomaticAtTimeOfCall,
    isStillSymptomatic,
];

export default indexRules;

// Related methods
const updateDateOfSymptomOnset = (data: DTO<AllowedVersions['index']>) => {
    // Only when "no" is explicitly selected, we update the dateOfSymptomOnset
    if (isSymptomatic(data) === false && data.test.dateOfSymptomOnset != data.test.dateOfTest) {
        return {
            'test.dateOfSymptomOnset': data.test.dateOfTest,
        };
    }

    return {};
};

const updateInfectiousnessDate = (data: DTO<AllowedVersions['index']>) => {
    const testFragment = data.test;
    if (!testFragment) return {};

    // Calculate infectiousness based on symptoms and EZD
    const symptomatic = isSymptomatic(data);

    if (symptomatic && testFragment.dateOfSymptomOnset) {
        const d = new Date(testFragment.dateOfSymptomOnset);
        d.setDate(d.getDate() - 2);
        const dateString = formatDate(d, 'yyyy-MM-dd');

        return {
            'test.dateOfInfectiousnessStart': dateString,
        };
    } else if (testFragment.dateOfTest) {
        return {
            'test.dateOfInfectiousnessStart': testFragment.dateOfTest,
        };
    }

    return {};
};
