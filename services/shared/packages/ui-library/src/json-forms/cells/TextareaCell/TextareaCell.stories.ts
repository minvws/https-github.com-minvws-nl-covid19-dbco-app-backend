import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import TextareaCell from './TextareaCell.vue';
import { merge } from 'lodash';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = { type: 'object', properties: { value: { type: 'string' } } };
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value', options: { multi: true } };

export default { title: 'JsonForms/Cells/TextareaCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TextareaCell,
        data: { value: '' },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Placeholder = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: TextareaCell,
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
        cell: TextareaCell,
        data: { value: '' },
        schema,
        uiSchema: merge({}, uiSchema, {
            options: {
                focus: true,
            },
        }),
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});
