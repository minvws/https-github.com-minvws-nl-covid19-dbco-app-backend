import type { Meta } from '@storybook/vue';
import type { FormData, JsonSchema, UiSchema } from '../../types';
import { setupStory } from '../../../../docs/utils/story';
import { default as JsonFormsChildStory } from './JsonFormsChildStory.vue';

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
    title: 'JsonForms/Core/JsonFormsChild',
    parameters: {
        docs: {
            description: {
                component:
                    'JsonFormsChild is used to handle the data from a single form. It adds the possibility of using the `$links` meta data to push data to an endpoint.',
            },
        },
    },
} as Meta;

export const Default = setupStory({
    components: { JsonFormsChildStory },
    props: {
        schema,
        uiSchema,
        data,
    },
    template: `<JsonFormsChildStory v-bind="props" />`,
});
