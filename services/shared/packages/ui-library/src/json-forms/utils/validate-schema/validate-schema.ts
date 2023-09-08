import { createAjv } from '@jsonforms/core';
import type { JsonSchema } from '../../types';

const ajv = createAjv({
    allErrors: true,
    verbose: true,
    strict: false,
});

/**
 * Validate a schema and return an error message if it is invalid.
 */
export function validateSchema(schema: JsonSchema) {
    try {
        ajv.compile(schema);
    } catch (error) {
        return (error as Error)?.message ?? 'Unknown error';
    }
}
