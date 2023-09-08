import type { SchemaRule } from '@/components/form/ts/schemaType';
import { unflatten } from './object';

export const processRules = (rules: SchemaRule<AnyObject>[], oldValues: AnyObject, newValues: AnyObject) => {
    const data = unflatten(newValues);

    rules.forEach((rule) => {
        const fields = Array.isArray(rule.watch) ? rule.watch : [rule.watch];

        const newVal = fields.map((field) => newValues[field]);
        const oldVal = fields.map((field) => oldValues[field]);
        if (JSON.stringify(newVal) === JSON.stringify(oldVal)) return;

        const changes = rule.callback(data, newVal, oldVal);

        newValues = { ...newValues, ...changes };
    });

    return newValues;
};

/**
 * Get the names from all inputs in the schema, also returns inputs from nested children schema's
 *
 * @param schema VueFormulate schema object
 * @returns string[] input names
 */
export const getInputNames = (schema: AnyObject[]): string[] =>
    schema.reduce((acc: string[], input: AnyObject) => {
        let result = acc;
        if (input.name && typeof input.name === 'string') result = [...result, input.name];
        if (input.children && Array.isArray(input.children)) result = [...result, ...getInputNames(input.children)];

        return result;
    }, []);
