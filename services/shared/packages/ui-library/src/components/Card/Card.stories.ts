import type { Meta, StoryFn } from '@storybook/vue';
import Card from './Card.vue';
import { TestBox } from '../../../docs/components';

const asValues = ['div', 'section', 'article'] as const;
const story: Meta = {
    title: 'Components/Card',
    component: Card,
    parameters: {
        docs: {
            description: {
                component: 'A Card component for displaying content such as events.',
            },
        },
    },
    argTypes: {
        as: { options: asValues, control: { type: 'select' }, default: 'div' },
        title: { control: { type: 'text' } },
        noPadding: { control: { type: 'boolean' } },
    },
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ ...args }) => ({
        components: { Card, TestBox },
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
    <Card v-bind="args">
        <TestBox class="tw-p-10 tw-w-full" color="blue" >
            Content
        </TestBox>
    </Card>
    `,
});

export default story;
