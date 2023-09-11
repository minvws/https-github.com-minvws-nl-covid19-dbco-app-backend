import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import NumberCell from './NumberCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'number' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default { title: 'JsonForms/Cells/NumberCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: NumberCell,
        data: { value: undefined },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: NumberCell,
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
