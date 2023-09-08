import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsControlStoryProps } from '../../stories';
import { JsonFormsControlStory } from '../../stories';

export default { title: 'JsonForms/Controls/InputControl' } as Meta;

export const String = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: '' },
        schema: { type: 'object', properties: { value: { type: 'string' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const StringRequired = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: '' },
        schema: { type: 'object', required: ['value'], properties: { value: { type: 'string' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const StringMultiline = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: '' },
        schema: { type: 'object', required: ['value'], properties: { value: { type: 'string' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
            options: {
                multi: true,
            },
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const StringWithErrors = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: { value: '' },
        schema: { type: 'object', required: ['value'], properties: { value: { type: 'string' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: 'Perferendis impedit',
        },
        additionalErrors: [
            {
                instancePath: '/value',
                message: 'New error',
                schemaPath: '',
                keyword: '',
                params: {},
            },
        ],
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});
