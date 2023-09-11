import type { Meta, StoryFn } from '@storybook/vue';
import { ref } from 'vue';
import { Button } from '../..';
import { TestBox } from '../../../docs/components';
import { useCollapse } from './use-collapse';

const story: Meta = {
    title: 'Composables/use-collapse',
    parameters: {
        docs: {
            description: {
                component: '`use-collapse` can be used to show and hide content using a collapse animation.',
            },
        },
    },
    argTypes: {
        initialIsOpen: { control: 'boolean' },
        collapsedSize: { control: 'number' },
    },
};

interface StoryProps {
    initialIsOpen: boolean;
    collapsedSize: number;
}

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn<StoryProps> {
    const Story: StoryFn<StoryProps> = ({ initialIsOpen, collapsedSize }) => ({
        components: { Button, TestBox },
        setup() {
            const isOpen = ref(initialIsOpen);
            const toggle = () => (isOpen.value = !isOpen.value);
            const { collapseRef } = useCollapse({ isOpen, collapsedSize });

            return { collapseRef, toggle, ...props };
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
    <div>
        <div class="tw-mb-6">
            <Button @click="toggle">Toggle</Button>
        </div>
        <div ref="collapseRef">
            <TestBox class="tw-p-10 tw-w-full" color="blue" >
                Content
            </TestBox>
        </div>
    </div>
    `,
});

export const LongContent = setupStory({
    template: `
    <div>
        <div class="tw-mb-6">
            <Button @click="toggle">Toggle</Button>
        </div>
        <div ref="collapseRef">
            <TestBox class="tw-p-10 tw-w-full" color="green" style="height: 800px;">
                Content
            </TestBox>
        </div>
    </div>
    `,
});

export default story;
