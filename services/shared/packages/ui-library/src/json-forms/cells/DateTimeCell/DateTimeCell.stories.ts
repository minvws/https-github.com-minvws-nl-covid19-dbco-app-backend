import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import DateTimeCell from './DateTimeCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'string', format: 'date-time' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default {
    title: 'JsonForms/Cells/DateTimeCell',
    parameters: {
        docs: {
            description: {
                component:
                    'This is a cell renderer for a date-time string. __It is important to note that it does not support time zones at this time.__ It will also not use (and drop) the seconds / milliseconds of the provided date-time string.',
            },
        },
    },
} as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: DateTimeCell,
        data: { value: '2000-01-01T15:55:35' },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: DateTimeCell,
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
