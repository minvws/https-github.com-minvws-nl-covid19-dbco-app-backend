import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import OneOfEnumCell from './OneOfEnumCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const oneOfEnumSchema: JsonSchema = {
    type: 'object',
    properties: {
        value: {
            type: 'string',
            oneOf: [
                {
                    const: 'foo',
                    title: 'Foo',
                },
                {
                    const: 'bar',
                    title: 'Bar',
                },
                {
                    const: 'foobar',
                    title: 'FooBar',
                },
            ],
        },
    },
};

const oneOfNumberEnumSchema: JsonSchema = {
    type: 'object',
    properties: {
        value: {
            type: 'number',
            oneOf: [
                {
                    const: 0,
                    title: 'Nothing',
                },
                {
                    const: 2.5,
                    title: 'Some',
                },
                {
                    const: 9999.99,
                    title: 'Many',
                },
            ],
        },
    },
};

const oneOfBooleanEnumSchema: JsonSchema = {
    type: 'object',
    properties: {
        value: {
            type: 'boolean',
            oneOf: [
                {
                    const: false,
                    title: 'Absolutely no',
                },
                {
                    const: true,
                    title: 'Yes please',
                },
            ],
        },
    },
};

const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default {
    title: 'JsonForms/Cells/OneOfEnumCell',
} as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: OneOfEnumCell,
        data: {},
        schema: oneOfEnumSchema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const OneOfNumber = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: OneOfEnumCell,
        data: {},
        schema: oneOfNumberEnumSchema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const OneOfBoolean = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: OneOfEnumCell,
        data: {},
        schema: oneOfBooleanEnumSchema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});
