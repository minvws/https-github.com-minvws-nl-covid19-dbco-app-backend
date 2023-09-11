import type { JsonSchemaType } from '../../types';

export type StringConversionValue = string | number | boolean | undefined;
export type StringConversionType = Extract<JsonSchemaType, 'string' | 'number' | 'integer' | 'boolean'>;

export function inputToNumber(value: string) {
    return value === '' ? undefined : Number(value);
}

export function inputToInteger(value: string) {
    return value === '' ? undefined : parseInt(value, 10);
}

/**
 * Converts a string back to the type given.
 * It only converts the type of the value, not the value itself.
 * e.g. it does not change a float into an integer, only from string back to number.
 */
export function stringToValue(value: string, type: StringConversionType): StringConversionValue {
    if (value === '') return undefined;

    switch (type) {
        case 'string':
            return value;
        case 'number':
        case 'integer':
            return Number(value);
        case 'boolean':
            return value === 'true';
        default:
            throw new Error(`Unknown JSON schema type ${type}`);
    }
}

export function valueToString(value: StringConversionValue): string {
    if (value === undefined) return '';
    return value.toString();
}
