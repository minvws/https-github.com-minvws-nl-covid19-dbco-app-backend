import type { Meta, StoryFn } from '@storybook/vue';
import Heading from './Heading.vue';
import { InfoLabel } from '../../../docs/components';

const asValues = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'] as const;
const sizeValues = ['xs', 'sm', 'md', 'lg', 'xl', '2xl'] as const;

interface Props {
    content: string;
    as: typeof asValues;
    size: typeof sizeValues;
}

const story: Meta = {
    title: 'Components/Heading',
    component: Heading,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/aFJlQTQ4XzGF18OsiRVqJa/%F0%9F%8E%A8-Fundamentals',
        },
    },
    args: {
        content:
            'Repellat aperiam laudantium vel eligendi assumenda dolorem delectus ut nesciunt veniam necessitatibus nobis.',
    },
    argTypes: {
        content: {
            control: 'text',
        },
        as: { options: asValues, control: { type: 'select' } },
        size: { options: sizeValues, control: { type: 'select' } },
        strong: {
            control: 'boolean',
        },
    },
};

const Template: StoryFn<Props> = ({ content, ...args }) => {
    return {
        components: { Heading },
        setup() {
            return { args, content };
        },
        template: `<Heading v-bind="args">{{content}}</Heading>`,
    };
};

const SizesTemplate: StoryFn<Props> = ({ content, ...args }) => {
    return {
        components: { Heading, InfoLabel },
        setup() {
            return { args, content, sizeValues };
        },
        template: `
        <div>
            <div v-for="size in sizeValues" class='tw-mb-4'>
                <Heading v-bind="args" :size='size'>{{content}}</Heading>
                <InfoLabel>
                    {{ size }}
                </InfoLabel>
            </div>
        </div>`,
    };
};

export const Default = Template.bind({});

export const Sizes = SizesTemplate.bind({});

export default story;
