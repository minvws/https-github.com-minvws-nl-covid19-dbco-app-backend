import type { JsonSchema } from '@dbco/ui-library';

/**
 * Schema will be wrapped in a `data` property by BE
 */
export const schema: JsonSchema = {
    type: 'object',
    properties: {
        foo: {
            type: 'object',
            properties: {
                bar: {
                    type: 'string',
                },
            },
        },
    },
};
