import type {
    ChildFormCollectionJsonSchema,
    ChildFormControlElement,
    FormCollectionData,
    FormData,
    JsonSchema,
    UiSchema,
} from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data: FormData = {
    children: {
        items: [
            {
                name: 'Minus dolor',
                $links: {
                    update: { href: '/api/user/1', method: 'POST' },
                },
            },
            {
                name: 'Porro occaecati',
                $links: {
                    update: { href: '/api/user/2', method: 'POST' },
                },
            },
        ],
        $links: {
            get: { href: '/api/users' },
            create: { href: '/api/users/create', method: 'POST' },
        },
    } as FormCollectionData,
    $links: {
        update: { href: '/api/members', method: 'POST' },
    },
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        children: {
            type: 'object',
            properties: {
                items: {
                    type: 'array',
                    items: {
                        type: 'object',
                        properties: {
                            name: { type: 'string' },
                        },
                    },
                },
            },
        } as ChildFormCollectionJsonSchema,
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/children',
            customRenderer: 'ChildFormCollection',
            options: {
                detail: {
                    type: 'Control',
                    scope: '#/properties/name',
                },
            },
        } as ChildFormControlElement,
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
};
