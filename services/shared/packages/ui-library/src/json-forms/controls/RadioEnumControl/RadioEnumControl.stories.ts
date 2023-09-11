import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import { JsonFormsControlStory } from '../../stories';
import { cloneDeep, set } from 'lodash';

const data = {};

const schema = {
    type: 'object',
    properties: { value: { type: 'string', enum: ['one', 'two', 'three'] } },
};

const uiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            label: 'Enim fugit maxime',
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

export default { title: 'JsonForms/Controls/RadioEnumControl' } as Meta;
