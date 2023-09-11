import type { JsonSchema, UiSchema } from '@dbco/ui-library';
import { testForms } from '../../../test-forms/test-forms';

export const data = {
    name: '',
    code: '',
    testForm: '',
};

export const schema: JsonSchema = {
    type: 'object',
    required: ['code', 'name'],
    properties: {
        name: {
            type: 'string',
        },
        code: {
            type: 'string',
        },
        testForm: {
            type: 'string',
            oneOf: Object.keys(testForms).map((x) => ({ const: x })),
        },
    },
};

export const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'HorizontalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/name',
                },
                {
                    type: 'Control',
                    scope: '#/properties/code',
                },
            ],
        },
        {
            type: 'Control',
            label: 'Voeg een test formulier toe',
            scope: '#/properties/testForm',
        },
    ],
};
