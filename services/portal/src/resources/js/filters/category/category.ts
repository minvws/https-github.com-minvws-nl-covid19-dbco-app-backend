import { contextCategoryToGroupMap } from '@/components/form/ts/formOptions';

const categories = {
    '1': '1 - Huisgenoot',
    '2a': '2A - Nauw contact',
    '2b': '2B - Nauw contact',
    '3a': '3A - Overig contact',
    '3b': '3B - Overig contact',
};

type CategoryKey = keyof typeof categories;
type ContextCategoryMapKey = keyof typeof contextCategoryToGroupMap;

/**
 * @example
 * categoryFormat('1') // '1 - Huisgenoot'
 */
export function categoryFormat(value?: CategoryKey | string | null) {
    if (!value) return '';

    return categories[value.toLowerCase() as CategoryKey] || value;
}

/**
 * @example
 * placeCategoryImageClass(ContextCategoryV1.VALUE_restaurant) // 'icon--category-horeca'
 */
export function placeCategoryImageClass(value?: ContextCategoryMapKey | string | null) {
    const categoryGroup = contextCategoryToGroupMap[value as ContextCategoryMapKey];

    return `icon--category-${categoryGroup || 'onbekend'}`;
}
