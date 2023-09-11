import type { JsonSchema, UiSchema } from '../../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    minLength10: 'Adulthood',
    maxLength10: 'Icebreakers',
    enum: 'four',
    type: 4,
    const: 'bar',
    pattern: '12A',
    minimum10: 9,
    maximum10: 11,
    exclusiveMinimum10: 10,
    exclusiveMaximum10: 10,
    multipleOf10: 12,
    minItems2: [1],
    maxItems2: [1, 2, 3],
    uniqueItems: [1, 1, 3],
};

const schema: JsonSchema = {
    type: 'object',
    required: ['required'],
    properties: {
        minLength10: { type: 'string', minLength: 10 },
        maxLength10: { type: 'string', maxLength: 10 },
        required: { type: 'string' },
        enum: { type: 'string', enum: ['one', 'two', 'three'] },
        type: { type: 'string' },
        pattern: { type: 'string', pattern: '^[0-9]{3}$' },
        minimum10: { type: 'number', minimum: 10 },
        maximum10: { type: 'number', maximum: 10 },
        exclusiveMinimum10: { type: 'number', exclusiveMinimum: 10 },
        exclusiveMaximum10: { type: 'number', exclusiveMaximum: 10 },
        multipleOf10: { type: 'number', multipleOf: 10 },
        minItems2: { type: 'array', items: { type: 'number' }, minItems: 2 },
        maxItems2: { type: 'array', items: { type: 'number' }, maxItems: 2 },
        uniqueItems: { type: 'array', items: { type: 'number' }, uniqueItems: true },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: Object.keys(schema.properties!).map((key) => {
        return {
            type: 'Control',
            scope: `#/properties/${key}`,
        };
    }),
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};
