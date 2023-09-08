import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import TimeCell from './TimeCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'string', format: 'time' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default {
    title: 'JsonForms/Cells/TimeCell',
    parameters: {
        docs: {
            description: {
                component: 'This is a cell renderer for a time string.',
            },
        },
    },
} as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TimeCell,
        data: { value: '15:55:35' },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TimeCell,
        data: { value: '' },
        schema,
        uiSchema: {
            ...uiSchema,
            options: {
                focus: true,
            },
        },
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});
