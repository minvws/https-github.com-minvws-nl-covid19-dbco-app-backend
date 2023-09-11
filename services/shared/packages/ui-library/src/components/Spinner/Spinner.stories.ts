import type { Meta, StoryFn } from '@storybook/vue';
import Spinner from './Spinner.vue';
import { InfoLabel } from '../../../docs/components';

const sizeValues = ['sm', 'md', 'lg'] as const;

interface Props {
    content: string;
    size: typeof sizeValues;
}

const story: Meta = {
    title: 'Components/Spinner',
    component: Spinner,
    argTypes: {
        size: { options: sizeValues, control: { type: 'select' } },
    },
};

const Template: StoryFn<Props> = ({ ...args }) => {
    return {
        components: { Spinner },
        setup() {
            return { args };
        },
        template: `<Spinner v-bind="args" />`,
    };
};

const SizesTemplate: StoryFn<Props> = () => {
    return {
        components: { Spinner, InfoLabel },
        setup() {
            return { sizeValues };
        },
        template: `
        <div>
            <div v-for="size in sizeValues" class='tw-mb-4'>
                <Spinner :size='size' />
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
