import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsControlStoryProps } from '../../stories';
import { JsonFormsControlStory } from '../../stories';

export default {
    title: 'JsonForms/Layouts/HorizontalLayout',
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?type=design&node-id=529-4065&t=CHj828vzKphLQVV3-0',
        },
    },
} as Meta;

export const Default = setupStory<JsonFormsControlStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data: {},
        schema: {
            type: 'object',
            properties: {
                one: { type: 'string' },
                two: { type: 'string' },
                three: { type: 'string' },
            },
        },
        uiSchema: {
            type: 'HorizontalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/one',
                },
                {
                    type: 'Control',
                    scope: '#/properties/two',
                },
                {
                    type: 'Control',
                    scope: '#/properties/three',
                },
            ],
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});
