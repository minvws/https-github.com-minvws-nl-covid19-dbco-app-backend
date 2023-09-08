import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import EnumCell from './EnumCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const enumSchema: JsonSchema = {
    type: 'object',
    properties: { value: { type: 'string', enum: ['one', 'two', 'three'] } },
};

const enumIntegerSchema: JsonSchema = {
    type: 'object',
    properties: { value: { type: 'integer', enum: [0, 1, 2] } },
};

const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default {
    title: 'JsonForms/Cells/EnumCell',
    parameters: {
        docs: {
            description: {
                component:
                    'This is a cell renderer for a string enum. __It is important to note that it does not support other schema type enums such as integer or number (yet)__',
            },
        },
    },
} as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: EnumCell,
        data: {},
        schema: enumSchema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Integer = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: EnumCell,
        data: {},
        schema: enumIntegerSchema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Placeholder = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: EnumCell,
        data: {},
        schema: enumSchema,
        uiSchema: {
            ...uiSchema,
            options: {
                placeholder: 'Cum atque placeat voluptatum eius aliquam officia quae?',
            },
        },
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: EnumCell,
        data: {},
        schema: enumSchema,
        uiSchema: {
            ...uiSchema,
            options: {
                focus: true,
            },
        },
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});
