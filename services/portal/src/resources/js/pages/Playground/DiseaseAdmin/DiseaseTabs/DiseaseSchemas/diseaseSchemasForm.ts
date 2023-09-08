import type { JsonSchema, UiSchema } from '@dbco/ui-library';

export const data = {
    dossierSchema: '{}',
    contactSchema: '{}',
    eventSchema: '{}',
    sharedDefs: '[]',
};

export const schema: JsonSchema = {
    type: 'object',
    required: ['dossierSchema', 'contactSchema', 'eventSchema'],
    properties: {
        dossierSchema: {
            type: 'string',
        },
        contactSchema: {
            type: 'string',
        },
        eventSchema: {
            type: 'string',
        },
        sharedDefs: {
            type: 'string',
        },
    },
};

export const uiSchema: UiSchema = {
    type: 'Categorization',
    elements: [
        {
            type: 'Category',
            label: 'Dossier',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/dossierSchema',
                    customRenderer: 'JsonEditor',
                },
            ],
        },
        {
            type: 'Category',
            label: 'Contact',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/contactSchema',
                    customRenderer: 'JsonEditor',
                },
            ],
        },
        {
            type: 'Category',
            label: 'Event',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/eventSchema',
                    customRenderer: 'JsonEditor',
                },
            ],
        },
        {
            type: 'Category',
            label: 'Shared',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/sharedDefs',
                    customRenderer: 'JsonEditor',
                },
            ],
        },
    ],
};
