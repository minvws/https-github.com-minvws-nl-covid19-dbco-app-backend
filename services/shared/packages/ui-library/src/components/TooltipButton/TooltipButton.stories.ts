import type { Meta, StoryFn } from '@storybook/vue';
import Icon from '../Icon/Icon.vue';
import TooltipButton from './TooltipButton.vue';
import { iconNames } from '../Icon/icons';
import type { Position } from './TooltipButton';

const positionOptions: Array<Position> = ['top', 'right', 'left'];

const story: Meta = {
    title: 'Components/TooltipButton',
    component: TooltipButton,
    parameters: {
        docs: {
            description: {
                component: 'A Tooltip component for displaying content when hovering a button.',
            },
        },
    },
    args: {
        content: 'Content',
    },
    argTypes: {
        content: { control: { type: 'text' } },
        icon: { control: { type: 'select' }, options: iconNames },
        position: { control: { type: 'select' }, options: positionOptions },
    },
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ content, ...args }) => ({
        components: { TooltipButton, Icon },
        setup() {
            return { args, content, ...props };
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
    <div class="tw-p-32">
        <TooltipButton v-bind="args">{{ content }}</TooltipButton>
    </div>
`,
});

export const TooltipButtonWithMarkupContent = setupStory({
    template: `
    <div class="tw-p-32">
        <TooltipButton>
            <p class="tw-m-0">
                <Icon
                    name="circle-blue"
                    class="tw-mr-1 tw-w-2 tw-shrink-0"
                    role="img"
                    title="source dates"
                />Binnen de bronperiode
            </p>
            <p class="tw-m-0">
                <Icon
                    name="square-red"
                    class="tw-mr-1 tw-w-2 tw-shrink-0"
                    role="img"
                    title="infectious dates"
                />Binnen de besmettelijke periode
            </p>
        </TooltipButton>
    </div>
`,
});

export const TooltipButtonWithPositionOptions = setupStory({
    props: {
        positionOptions,
    },
    template: `
    <div class="tw-p-32">
        <div v-for='position in positionOptions' :key="position" class='tw-mb-4'>
            <TooltipButton v-bind="args" :position="position">{{ content }}</TooltipButton>
        </div>
    </div>
`,
});

export default story;
