import type { Meta } from '@storybook/vue';
import type { FormData, JsonSchema, UiSchema } from '../../types';
import { setupStory } from '../../../../docs/utils/story';
import { default as JsonFormsStory } from './JsonFormsStory.vue';

const data: FormData = {
    user: {
        firstName: 'Placeat',
        lastName: 'Quod',
    },
    $links: {
        self: { href: '/api/user/1' },
        update: { href: '/api/user/1', method: 'POST' },
    },
    $validationErrors: [
        {
            instancePath: '/user/lastName',
            message: 'Laborum nesciunt aspernatur vero doloribus fuga quam porro possimus.',
            keyword: '',
            params: {},
        },
    ],
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

export default {
    title: 'JsonForms/Core/JsonForms',
    parameters: {
        docs: {
            description: {
                component:
                    'JsonForms is the main component that enables all the JsonForms functionality. It provides the option to pass in an `actionHandler` to handle async api calls for the form data. For more examples check out the `Examples` section.',
            },
        },
    },
} as Meta;

export const Default = setupStory({
    components: { JsonFormsStory },
    props: {
        schema,
        uiSchema,
        data,
    },
    template: `<JsonFormsStory v-bind="props" />`,
});
