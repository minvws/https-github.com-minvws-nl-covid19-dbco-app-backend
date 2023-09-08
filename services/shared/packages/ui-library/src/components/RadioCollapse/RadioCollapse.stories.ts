import type { Meta, StoryFn } from '@storybook/vue';
import RadioCollapse from './RadioCollapse.vue';
import { TestBox } from '../../../docs/components';
import { Button } from '../..';

const story: Meta = {
    title: 'Components/RadioCollapse',
    component: RadioCollapse,
    parameters: {
        docs: {
            description: {
                component: 'A component for collapsing with a radio group.',
            },
        },
    },
    args: {
        openButtonLabel: 'Ja',
        closeButtonLabel: 'Nee',
        initialIsOpen: false,
        title: 'Title',
    },
    argTypes: {
        initialIsOpen: { control: 'boolean' },
        openButtonLabel: { control: 'text' },
        closeButtonLabel: { control: 'text' },
        title: { control: 'text' },
    },
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ ...args }) => ({
        components: { RadioCollapse, TestBox, Button },
        setup() {
            return { args, ...props };
        },
        template,
    });

    Story.parameters = {
        docs: {
            description: {
                story: description,
            },
        },
    };

    return Story;
}

export const Default = setupStory({
    template: `
    <RadioCollapse v-bind="args">
        <TestBox class="tw-p-10 tw-w-full" color="gray" >
            Content
        </TestBox>
    </RadioCollapse>
    `,
});

export default story;
