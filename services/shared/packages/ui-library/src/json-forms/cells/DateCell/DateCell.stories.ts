import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import DateCell from './DateCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'string', format: 'date' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default { title: 'JsonForms/Cells/DateCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: DateCell,
        data: { value: '2000-01-01' },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: DateCell,
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
