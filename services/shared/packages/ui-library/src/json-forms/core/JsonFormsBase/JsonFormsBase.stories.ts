import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import JsonFormsBase from './JsonFormsBase.vue';
import JsonFormsBaseStory from './JsonFormsBaseStory.vue';

const data = {
    name: 'John',
    likesChocolate: false,
    favorites: [{ brand: 'Tony chocolonely', flavor: 'sea salt caramel', type: 'milk' }],
};

const schema = {
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
            items: { $ref: '#/$defs/chocolate' },
        },
    },
    $defs: {
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

const uiSchema = {
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
                    scope: '#/properties/likesChocolate',
                },
            ],
        },
        {
            type: 'Control',
            label: 'My favorite chocolate',
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
                        },
                    ],
                },
            },
        },
    ],
};

const additionalErrors = [
    {
        instancePath: '/name',
        message: 'New name error',
        schemaPath: '',
        keyword: '',
        params: {},
    },
];

export default {
    title: 'JsonForms/Core/JsonFormsBase',
    component: JsonFormsBase,
    parameters: {
        docs: {
            description: {
                component:
                    'JsonFormsBase is a pre-configured Vue 2 [JsonForms](https://jsonforms.io/) component for rendering forms based on JSON schemas.',
            },
        },
    },
} as Meta;

export const Default = setupStory({
    components: { JsonFormsBaseStory },
    props: {
        schema,
        uiSchema,
        data,
        additionalErrors,
    },
    template: `<JsonFormsBaseStory v-bind="props" />`,
});
