import type { Meta } from '@storybook/vue';
import FormErrors from './FormErrors.vue';
import { setupStory } from '../../../docs/utils/story';

export default {
    title: 'Components/FormErrors',
    component: FormErrors,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?type=design&node-id=373-870&t=8wxnMKKrOyTdYSDD-0',
        },
    },
    args: {
        messages: [
            'Repellat aperiam laudantium vel eligendi assumenda dolorem delectus ut nesciunt veniam necessitatibus nobis.',
            'Voluptatum numquam non neque veniam eius officiis possimus.',
        ],
    },
    argTypes: {
        errors: {
            control: 'object',
        },
    },
} as Meta;

export const Default = setupStory({
    components: { FormErrors },
    template: `<FormErrors v-bind="args" />`,
});
