import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import { JsonFormsControlStory } from '../../stories';

const data = {};

const schema = {};

const uiSchema = {
    type: 'Categorization',
    elements: [
        {
            type: 'Category',
            label: 'Exercitationem',
            elements: [],
        },
        {
            type: 'Category',
            label: 'Vero deleniti',
            elements: [],
        },
        {
            type: 'Category',
            label: 'Provident',
            elements: [],
        },
    ],
};

export const Default = setupStory({
    components: { JsonFormsControlStory },
    props: {
        schema,
        uiSchema,
        data,
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export default { title: 'JsonForms/Layouts/Category' } as Meta;
