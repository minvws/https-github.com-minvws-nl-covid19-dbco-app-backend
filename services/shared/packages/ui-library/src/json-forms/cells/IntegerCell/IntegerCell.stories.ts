import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import IntegerCell from './IntegerCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'integer' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default { title: 'JsonForms/Cells/IntegerCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: IntegerCell,
        data: { value: undefined },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: IntegerCell,
        data: { value: undefined },
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
