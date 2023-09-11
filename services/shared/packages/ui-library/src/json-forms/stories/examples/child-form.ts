import type { FormData, JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data: FormData = {
    child: {
        name: 'Quaerat soluta',
        $links: {
            update: { href: '/api/company/1', method: 'POST' },
        },
    },
    $links: {
        update: { href: '/api/user/1', method: 'POST' },
    },
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        child: {
            type: 'object',
            properties: {
                name: { type: 'string' },
            },
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/child',
            customRenderer: 'ChildForm',
            options: {
                detail: {
                    label: 'Child form - name',
                    type: 'Control',
                    scope: '#/properties/name',
                },
            },
        },
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
};
