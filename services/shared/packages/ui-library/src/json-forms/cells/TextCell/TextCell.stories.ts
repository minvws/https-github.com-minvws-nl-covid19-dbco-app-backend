import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import TextCell from './TextCell.vue';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'string' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default { title: 'JsonForms/Cells/TextCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TextCell,
        data: { value: '' },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Placeholder = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TextCell,
        data: { value: '' },
        schema,
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
        cell: TextCell,
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
