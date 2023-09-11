import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsCellStoryProps } from '../../stories';
import { JsonFormsCellStory } from '../../stories';
import BooleanCell from './BooleanCell.vue';
import { merge } from 'lodash';
import type { JsonSchema, UiSchema } from '../../types';

const schema: JsonSchema = {
    type: 'object',
    properties: {
        value: { type: 'boolean' },
    },
};
const uiSchema: UiSchema = { type: 'Control', scope: '#/properties/value' };

export default { title: 'JsonForms/Cells/BooleanCell' } as Meta;

export const Default = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: BooleanCell,
        data: { value: false },
        schema,
        uiSchema,
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});

export const Focus = setupStory<JsonFormsCellStoryProps>({
    components: { JsonFormsCellStory },
    props: {
        cell: BooleanCell,
        data: { value: false },
        schema,
        uiSchema: merge({}, uiSchema, {
            options: {
                focus: true,
            },
        }),
    },
    template: `<JsonFormsCellStory v-bind="props" />`,
});
