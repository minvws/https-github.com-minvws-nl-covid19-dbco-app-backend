import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsControlStoryProps } from '../../stories';
import { JsonFormsControlStory } from '../../stories';

export default {
    title: 'JsonForms/Controls/ArrayControl',
    parameters: {
        docs: {
            description: {
                component: `The \`ArrayControl\` renderer is used to render data with the type \`array\`. When the [\`showSortButtons\` 
option is set to \`true\` in the \`ui-schema\`](https://jsonforms.io/docs/uischema/controls#sorting-buttons-showsortbuttons), the array items can be sorted using the buttons on the 
right side of the array item.

> Note that the [\`elementLabelProp\` and the collapisble content](https://jsonforms.io/docs/uischema/controls#label-for-array-elements-elementlabelprop) has not yet been implemented.
`,
            },
        },
    },
} as Meta;

export const Strings = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: {
            value: [
                'Illum nisi quos minus delectus ex tenetur necessitatibus quis tempore tenetur.',
                'Quia voluptatem voluptatem.',
                'Maiores vel sint omnis consectetur perferendis eius assumenda ex.',
            ],
        },
        schema: {
            type: 'object',
            required: ['value'],
            properties: { value: { type: 'array', items: { type: 'string' } } },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
            options: {
                addLabel: 'Suscipit a ab',
            },
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const StringsSortableWithErrors = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: {
            value: [
                'Illum nisi quos minus delectus ex tenetur necessitatibus quis tempore tenetur.',
                'Quia voluptatem voluptatem.',
                'Maiores vel sint omnis consectetur perferendis eius assumenda ex.',
            ],
        },
        schema: {
            type: 'object',
            required: ['value'],
            properties: {
                value: {
                    type: 'array',
                    items: { type: 'string' },
                    minItems: 5,
                },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
            options: {
                addLabel: 'Laudantium labore',
                showSortButtons: true,
            },
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const Objects = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: {
            value: [
                {
                    firstName: 'John',
                    lastName: 'Doe',
                },
                {
                    firstName: 'Jane',
                    lastName: 'Doe',
                },
            ],
        },
        schema: {
            type: 'object',
            required: ['value'],
            properties: {
                value: {
                    type: 'array',
                    items: {
                        type: 'object',
                        properties: {
                            firstName: {
                                type: 'string',
                            },
                            lastName: {
                                type: 'string',
                            },
                        },
                    },
                },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});
