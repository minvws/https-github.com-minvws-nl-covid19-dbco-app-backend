import type { ContextCategoryGroupV1 } from '@dbco/enum';
import { contextCategoryGroupV1Options, ContextCategoryV1, contextCategoryV1Options, YesNoUnknownV1 } from '@dbco/enum';

export const isYes = YesNoUnknownV1.VALUE_yes;
export const isNo = YesNoUnknownV1.VALUE_no;
export const isUnknown = YesNoUnknownV1.VALUE_unknown;

export const YES = '01';
export const NO = '00';

export const askedV1Options = {
    [YES]: 'Gevraagd',
    [NO]: 'Niet gevraagd',
};

export const yesNoUnknownV1LangOptions = {
    [YesNoUnknownV1.VALUE_no]: 'Nederlands',
    [YesNoUnknownV1.VALUE_yes]: 'Anders',
    [YesNoUnknownV1.VALUE_unknown]: 'Onbekend',
};

export const sourceV1Options = {
    [ContextCategoryV1.VALUE_thuis]: 'Thuissituatie (ziektegevallen onder huisgenoten of niet samenwonende partner)',
    [ContextCategoryV1.VALUE_bezoek]: 'Bezoek in de thuissituatie (van of bij familie, vrienden etc.)',
    [ContextCategoryV1.VALUE_groep]: 'Studentenhuis',
    [ContextCategoryV1.VALUE_feest]: 'Feest / Groepsbijeenkomst privésfeer (verjaardag, bruiloft, borrel, etc)',
    [ContextCategoryV1.VALUE_buitenland]: 'Buitenlandreis',
};

export const trueFalseV1Options = {
    [YES]: 'Ja',
    [NO]: 'Nee',
};

/**
 * Adds an empty option to the start of a Record type FormDropdownOptions.
 * The return type is casted to be the same as the input type, to please the
 * TypeScript checks of the fieldGenerators
 */
export const addEmptyOption = <T extends Record<string, string>>(options: T) =>
    ({
        // Add empty option
        '': 'Kies type',
        ...options,
    }) as T;

/**
 * Get all subcategories alphabetically ordered as { 'restaurant': 'Restaurant / Café', '...': '...', ... }
 */
export const allSources = (contextCategories: typeof contextCategoryV1Options) =>
    [...contextCategories]
        .sort((a, b) => -(a.label > b.label) || +(a.label < b.label))
        .reduce((c, subcat) => ({ ...{ [subcat.value]: subcat.label }, ...c }), {});

export const contextCategoryByGroup = (
    contextCategories: typeof contextCategoryV1Options,
    contextCategoryGroups: typeof contextCategoryGroupV1Options
) => {
    type ReturnType = {
        [key: string]: {
            title: string;
            values: { [key: string]: { label: string; description: string } };
        };
    };

    return Object.fromEntries(
        Object.values(contextCategoryGroups).map(({ label: groupLabel, value: groupValue }) => {
            const categories = contextCategories.filter((c) => c.group == groupValue);
            const values = Object.fromEntries(
                categories.map((category) => [
                    category.value,
                    {
                        label: category.label,
                        description: category.description,
                    },
                ])
            );

            return [
                groupValue,
                {
                    title: groupLabel,
                    values,
                },
            ];
        })
    ) as ReturnType;
};

/**
 * map categories to their group code (for image lookup), plus add the groups themselves as well
 */
export const contextCategoryToGroupMap: Record<ContextCategoryV1 | ContextCategoryGroupV1, string> = Object.fromEntries(
    [
        ...contextCategoryGroupV1Options.map(({ value }) => [value, value]),
        ...contextCategoryV1Options.map((e) => [e.value, e.group]),
    ]
);
