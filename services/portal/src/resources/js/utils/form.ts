import type { FormCondition, FormLabel, FormPropCondition, FormRuleCondition } from '@/components/form/ts/formTypes';
import { isPropCondition, FormConditionRule, isRuleCondition, isFieldCondition } from '@/components/form/ts/formTypes';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import type { RootStoreState } from '@/store';
import store from '@/store';
import { StoreType } from '@/store/storeType';
import { isMedicalPeriodInfoIncomplete, isMedicalPeriodInfoNotDefinitive, sourceDates } from '@/utils/case';
import {
    isPotentiallyVaccinated,
    isPreviouslyInfectedAndPotentiallyVaccinated,
    isRecentlyInfected,
} from '@/utils/caseImmunity';
import { BcoTypeV1 } from '@dbco/enum';
import _, { isArray } from 'lodash';
import { isBetweenDays, parseDate } from './date';
import { isRef } from 'vue';
import type { Store } from 'vuex';

/**
 * Returns the string value for a FormLabel argument
 */
export const formLabelValue = (label: FormLabel) => (typeof label === 'function' ? label() : label);

export const hasValues = (value: any) =>
    Array.isArray(value) ? value.length > 0 : value !== null && value !== undefined;

export const isExtensiveBCO = (caseFragments: CovidCaseUnionDTO) =>
    caseFragments.extensiveContactTracing.receivesExtensiveContactTracing !== BcoTypeV1.VALUE_standard;

/**
 * Checks if a FormCondition is met on the passed formStore values
 */
export const formConditionMet = (
    rootStore: Store<RootStoreState>,
    condition: FormCondition,
    formValues?: Record<any, any>,
    index?: number,
    storeType: StoreType = StoreType.INDEX
) => {
    if (isRef(condition)) {
        return condition.value;
    }

    if (isFieldCondition(condition)) {
        if (!formValues) {
            throw new Error('You used a FieldCondition in a non-Form context');
        }
        const value = extractValue(formValues, condition.field, index);
        const result = checkAgainstValues(value, condition.values);
        return condition.not ? !result : result;
    }

    if (isPropCondition(condition)) {
        const storeValues = storeDataProvider(condition, storeType, rootStore);
        const value = extractValue(storeValues, condition.prop, index);
        const result = checkAgainstValues(value, condition.values);
        return condition.not ? !result : result;
    }

    if (isRuleCondition(condition)) {
        const storeValues = storeDataProvider(condition, storeType, rootStore);
        const value = condition.prop ? extractValue(storeValues, condition.prop, index) : null;
        const result = formRuleMet(storeValues, condition.rule, value, storeType);
        return condition.not ? !result : result;
    }

    isNever(condition);
};

const isNever = (value: never): never => {
    throw new Error(`Unhandled discriminated union member: ${JSON.stringify(value)}`);
};

const storeDataProvider = (
    condition: FormPropCondition | FormRuleCondition,
    storeType: StoreType,
    rootStore: Store<RootStoreState>
): Record<any, any> => {
    const storeGetter = !isRef(condition) ? condition.getter ?? 'fragments' : 'fragments';
    return rootStore.getters[`${storeType}/${storeGetter}`];
};

function extractValue(
    data: Record<any, any>,
    property: string,
    index?: number
): string | number | boolean | null | undefined {
    const target = property.split('.');
    let value = data;
    while (value && target.length) {
        const prop = target.shift() as string;

        // If wildcard and it is an array
        // Select the next prop from each item
        if (prop === '*' && Array.isArray(value)) {
            const sub = target.shift() as string;
            value = value.map((item) => item[sub]);
        } else if (prop === '>' && Array.isArray(value) && index !== undefined && !isNaN(index)) {
            const sub = target.shift() as string;
            value = value[index]?.[sub];
        } else {
            value = value[prop];
        }
    }

    // Direct refactor, so types are still wonky
    return value as any as string | number | boolean | null | undefined;
}

const checkAgainstValues = (
    value: string | number | boolean | null | undefined,
    values: (string | number | boolean | null | undefined)[]
) => {
    // An empty array condition should check if the value is an empty array or nullish
    if (values.length === 0) {
        // On the safe side... false could maybe be a valid option?
        return (
            !value === undefined ||
            value === null ||
            value === '' ||
            (value !== undefined && isArray(value) && value.length === 0)
        );
    }

    return Array.isArray(value) ? value.some((v) => values.includes(v)) : values.includes(value);
};

export const formRuleMet = (storeFragments: any, rule: string, value: any, storeType: StoreType = StoreType.INDEX) => {
    switch (rule) {
        case FormConditionRule.DateInSourcePeriod: {
            // Source dates should be calculated with index fragments
            const indexFragments = store.getters['index/fragments'];
            value = !Array.isArray(value) ? [value] : value;
            const datesSource = sourceDates(indexFragments);
            if (!datesSource) return false;

            return value.some((cMoment: string) => {
                return isBetweenDays(parseDate(cMoment), datesSource.startDate, datesSource.endDate, '[]');
            });
        }
        case FormConditionRule.MedicalPeriodInfoIncomplete:
            return isMedicalPeriodInfoIncomplete(storeFragments);
        case FormConditionRule.MedicalPeriodInfoNotDefinitive:
            return !isMedicalPeriodInfoIncomplete(storeFragments) && isMedicalPeriodInfoNotDefinitive(storeFragments);
        case FormConditionRule.PotentiallyVaccinated:
            return isPotentiallyVaccinated(storeFragments, storeType);
        case FormConditionRule.PreviouslyInfectedAndPotentiallyVaccinated:
            return isPreviouslyInfectedAndPotentiallyVaccinated(storeFragments, storeType);
        case FormConditionRule.RecentlyInfected:
            return isRecentlyInfected(storeFragments, storeType);
        case FormConditionRule.HasValues: {
            return hasValues(value);
        }
        case FormConditionRule.HasValuesOrExtensiveBCO: {
            return hasValues(value) || isExtensiveBCO(storeFragments);
        }
        default:
            throw `formRuleMet: ${rule} does not exist.`;
    }
};

/**
 * Ensures an input resets to its original size
 */
export const resetInputDimensions = (eventTarget: HTMLElement) => {
    eventTarget.style.height = '';
    eventTarget.style.width = '';
};
export const transformToFormErrors = (errors: Record<string, string[]>) =>
    _.mapValues(errors, (errors) => JSON.stringify({ warning: errors }));
