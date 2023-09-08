import type { JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    number: 1,
    string: 'foo',
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        number: { type: 'number', minimum: 2, multipleOf: 2 },
        string: { type: 'string', minLength: 4, pattern: '^foobar$' },
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
