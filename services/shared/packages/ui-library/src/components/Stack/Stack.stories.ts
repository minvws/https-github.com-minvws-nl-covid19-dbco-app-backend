import type { Meta, StoryFn } from '@storybook/vue';
import { TestBox } from '../../../docs/components';
import { Stack, VStack, HStack } from '.';

const tag = ['div', 'section', 'article'];
const direction = ['column', 'row'];
const spacing = ['0', '0.5', '1', '2', '3', '4', '6', '8', '10'];

const story: Meta = {
    title: 'Components/Stack',
    component: Stack,
    parameters: {
        docs: {
            description: {
                component: [
                    'Stack is a layout component used to group elements together and apply a space between them.',
                    'Even though there are options for this within the tailwind framework. This component improves the readability and adds some restrictions.',
                    '',
                    'To stack elements in horizontal or vertical direction only, use the `HStack` or `VStack` components. You can also use the `Stack` component as well and pass the direction prop.',
                ].join('<br/>'),
            },
        },
    },
    argTypes: {
        tag: { control: { type: 'select' }, options: tag },
        direction: { control: { type: 'select' }, options: direction },
        spacing: { control: { type: 'select' }, options: spacing },
    },
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ label, ...args }) => ({
        components: { Stack, HStack, VStack, TestBox },
        setup() {
            return { args, label, ...props };
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
<Stack v-bind="args">
    <TestBox color='green' centerContent class="tw-w-[50px] tw-h-[50px]" v-for="n in 3" :key="n">
        {{n}}
    </TestBox>
</Stack>
`,
});

export const HorizontalOnlyStack = setupStory({
    template: `
<HStack v-bind="args">
    <TestBox color='green' centerContent class="tw-w-[50px] tw-h-[50px]" v-for="n in 3" :key="n">
        {{n}}
    </TestBox>
</HStack>
`,
});

export const VerticalOnlyStack = setupStory({
    template: `
<VStack v-bind="args">
    <TestBox color='green' centerContent class="tw-w-[50px] tw-h-[50px]" v-for="n in 3" :key="n">
        {{n}}
    </TestBox>
</VStack>
`,
});

export default story;
