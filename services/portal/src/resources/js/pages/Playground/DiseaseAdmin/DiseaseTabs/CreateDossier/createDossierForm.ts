import type { JsonSchema, UiSchema } from '@dbco/ui-library';

export const data = {
    data: '{}',
};

export const schema: JsonSchema = {
    type: 'object',
    required: ['data'],
    properties: {
        data: {
            type: 'string',
        },
    },
};

export const uiSchema: UiSchema = {
    type: 'HorizontalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/data',
            customRenderer: 'JsonEditor',
        },
    ],
};
