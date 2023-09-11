import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import { JsonFormsControlStory } from '../../stories';
import { cloneDeep, set } from 'lodash';

const data = {};

const schema = {
    type: 'object',
    required: ['value'],
    properties: {
        value: {
            type: 'string',
            oneOf: [
                {
                    const: 'yes',
                    title: 'Yes',
                },
                {
                    const: 'no',
                    title: 'No',
                },
                {
                    const: 'maybe',
                    title: 'Maybe',
                },
            ],
        },
    },
};

const uiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            label: 'Call me?',
            scope: '#/properties/value',
            options: {
                format: 'radio',
            },
        },
    ],
};

export const Default = setupStory({
    components: { JsonFormsControlStory },
    props: {
        schema,
        uiSchema,
        data,
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const ButtonVariant = setupStory({
    components: { JsonFormsControlStory },
    props: {
        schema,
        uiSchema: set(cloneDeep(uiSchema), 'elements.0.options.format', 'radio-button'),
        data,
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const AsBooleanCustomLabels = setupStory({
    components: { JsonFormsControlStory },
    props: {
        schema: {
            ...schema,
            properties: {
                value: {
                    type: 'boolean',
                    oneOf: [
                        {
                            const: true,
                            title: 'Heck yes!',
                        },
                        {
                            const: false,
                            title: 'Hell no!',
                        },
                    ],
                },
            },
        },
        uiSchema,
        data: {
            value: true,
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export default { title: 'JsonForms/Controls/RadioOneOfEnumControl' } as Meta;
