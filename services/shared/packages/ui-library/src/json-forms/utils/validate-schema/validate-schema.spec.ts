import type { JsonSchema } from '../../types';
import { validateSchema } from './validate-schema';

describe('validate-schema', () => {
    it('should return undefined if the schema is valid', () => {
        const schema: JsonSchema = {
            type: 'object',
            properties: {
                value: {
                    type: 'string',
                },
            },
        };
        const errorMessage = validateSchema(schema);
        expect(errorMessage).toBeUndefined();
    });

    it('should return an error message if the schema is NOT valid', () => {
        const schema: JsonSchema = {
            type: 'object',
            properties: {
                value: {
                    type: 'foobar' as any,
                },
            },
        };
        const errorMessage = validateSchema(schema);
        expect(errorMessage).includes('schema is invalid');
    });
});
