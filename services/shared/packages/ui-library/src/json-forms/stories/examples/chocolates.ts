import type { JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    name: 'John',
    likesChocolate: false,
    favorites: [{ brand: 'Tony chocolonely', flavor: 'sea salt caramel', type: 'milk' }],
};

const schema: JsonSchema = {
    type: 'object',
    required: ['name', 'likesChocolate'],
    properties: {
        name: {
            type: 'string',
            minLength: 3,
        },
        likesChocolate: {
            type: 'boolean',
        },
        favorites: {
            type: 'array',
            items: { $ref: '#/definitions/chocolate' },
        },
    },
    definitions: {
        chocolate: {
            type: 'object',
            required: ['brand', 'type'],
            properties: {
                brand: {
                    type: 'string',
                },
                flavor: {
                    type: 'string',
                },
                type: {
                    type: 'string',
                    oneOf: [
                        {
                            const: 'dark',
                        },
                        {
                            const: 'milk',
                        },
                        {
                            const: 'white',
                        },
                    ],
                },
            },
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/name',
        },
        {
            type: 'Control',
            scope: '#/properties/likesChocolate',
        },
        {
            type: 'Control',
            label: 'Favorite chocolates',
            scope: '#/properties/favorites',
            rule: {
                effect: 'SHOW',
                condition: {
                    scope: '#/properties/likesChocolate',
                    schema: {
                        const: true,
                    },
                },
            },
            options: {
                detail: {
                    type: 'HorizontalLayout',
                    elements: [
                        {
                            type: 'Control',
                            scope: '#/properties/brand',
                        },
                        {
                            type: 'Control',
                            scope: '#/properties/flavor',
                        },
                        {
                            type: 'Control',
                            scope: '#/properties/type',
                            options: {
                                placeholder: 'Select a type',
                            },
                        },
                    ],
                },
            },
        },
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};
