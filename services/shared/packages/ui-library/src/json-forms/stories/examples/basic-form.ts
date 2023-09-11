import type { JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    user: {
        firstName: 'Placeat',
        lastName: 'Quod',
    },
};

const schema: JsonSchema = {
    type: 'object',
    required: ['user'],
    properties: {
        user: {
            type: 'object',
            properties: {
                firstName: { type: 'string' },
                lastName: { type: 'string' },
            },
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/user/properties/firstName',
        },
        {
            type: 'Control',
            scope: '#/properties/user/properties/lastName',
        },
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};
