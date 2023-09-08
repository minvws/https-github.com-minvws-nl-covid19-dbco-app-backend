import type { Meta, StoryFn } from '@storybook/vue';

import Icon from './Icon.vue';
import { InfoLabel } from '../../../docs/components';
import { iconNames } from './icons';

interface Props {
    name: string;
}

const story: Meta = {
    title: 'Components/Icon',
    component: Icon,
    args: {
        name: iconNames[0],
    },
    argTypes: {
        name: { options: iconNames, control: { type: 'select' } },
    },
    parameters: {
        docs: {
            description: {
                component:
                    'Icons adopt the current text color by default. You can also change the icon color through CSS directly on the icon itself: `<Icon name="arrow-left" class="tw-text-green-600"/>`',
            },
        },
    },
};

const Template: StoryFn<Props> = ({ name }) => {
    return {
        components: { Icon },
        setup() {
            return { name };
        },
        template: `<Icon :name="name" />`,
    };
};
const ColoredTemplate: StoryFn<Props> = ({ name }) => {
    return {
        components: { Icon },
        setup() {
            return { name };
        },
        template: `<Icon :name="name" class="tw-text-green-600" />`,
    };
};
const AllIconsTemplate: StoryFn<Props> = () => {
    return {
        components: { Icon, InfoLabel },
        setup() {
            return { iconNames };
        },
        template: ` <div class="tw-flex tw-flex-wrap tw-justify-between">
                        <div v-for="name in iconNames" class='tw-mb-4 tw-block tw-p-4' :key="name">
                            <Icon :name="name" class="tw-block tw-mx-auto"/>
                            <InfoLabel class="tw-block tw-mt-2">{{ name }}</InfoLabel>
                        </div>
                    </div>`,
    };
};

export const Default = Template.bind({});
export const AllIcons = AllIconsTemplate.bind({});
export const ColoredIcon = ColoredTemplate.bind({});

export default story;
