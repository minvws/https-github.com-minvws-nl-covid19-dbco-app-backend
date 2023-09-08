import type { Meta, StoryFn } from '@storybook/vue';
import { Button, HStack, VStack } from '../..';
import { TestBox } from '../../../docs/components';
import { useOpenState } from './use-open-state';

const story: Meta = {
    title: 'Composables/use-open-state',
    parameters: {
        docs: {
            description: {
                component:
                    '`use-open-state` can be used to toggle show/hide or open/closed state. Includes optional onClose callback',
            },
        },
    },
};

interface StoryProps {
    onClose: () => void;
}

type StoryConfig = {
    template: string;
    description?: string;
};

function setupStory({ template, description }: StoryConfig): StoryFn<StoryProps> {
    const Story: StoryFn<StoryProps> = ({ onClose }) => ({
        components: { Button, HStack, TestBox, VStack },
        setup() {
            return useOpenState({ onClose });
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
    <VStack>
        <HStack>
            <Button @click="open">Open</Button>
            <Button @click="close">Close</Button>
        </HStack>
        <div v-if="isOpen">
            <TestBox class="tw-p-10 tw-w-full" color="blue" >
                Content
            </TestBox>
        </div>
    </VStack>
    `,
});

export default story;
