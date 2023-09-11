import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsControlStoryProps } from '../../stories';
import { JsonFormsControlStory } from '../../stories';

export default { title: 'JsonForms/Controls/BooleanControl' } as Meta;

export const Default = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: false },
        schema: { type: 'object', properties: { value: { type: 'boolean' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const DefaultRequired = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: false },
        schema: { type: 'object', required: ['value'], properties: { value: { type: 'boolean' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const DefaultRequiredTrue = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: false },
        schema: { type: 'object', properties: { value: { type: 'boolean', const: true } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});
