import type { Meta, StoryFn } from '@storybook/vue';
import Collapse from './Collapse.vue';
import { TestBox } from '../../../docs/components';
import { Button } from '../..';

const story: Meta = {
    title: 'Components/Collapse',
    component: Collapse,
    parameters: {
        docs: {
            description: {
                component: 'A component for collapsing content.',
            },
        },
    },
    args: {
        labelOpen: 'click to hide content',
        labelClosed: 'click to show content',
        intialCollapsed: false,
    },
    argTypes: {
        intialCollapsed: { control: 'boolean' },
        collapsedSize: { control: 'number' },
        labelOpen: { control: 'text' },
        labelClosed: { control: 'text' },
    },
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ ...args }) => ({
        components: { Collapse, TestBox, Button },
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
    <Collapse v-bind="args">
        <TestBox class="tw-p-10 tw-w-full" color="blue" >
            Content
        </TestBox>
    </Collapse>
    `,
});

export default story;
