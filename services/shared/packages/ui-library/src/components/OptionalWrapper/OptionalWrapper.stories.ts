import type { Meta, StoryFn } from '@storybook/vue';
import OptionalWrapper from './OptionalWrapper.vue';

interface StoryProps {
    shouldWrap: boolean;
}

const story: Meta = {
    title: 'Components/OptionalWrapper',
    component: OptionalWrapper,
    args: {
        shouldWrap: true,
    },
    argTypes: {
        shouldWrap: { control: 'boolean' },
    },
    parameters: {
        docs: {
            description: {
                component:
                    'This component allows you to optionally wrap another element. This can save you some duplicate styling you would otherwise have in an if/else case.',
            },
        },
    },
};

const Template: StoryFn<StoryProps> = ({ shouldWrap }) => {
    return {
        components: { OptionalWrapper },
        setup() {
            return { shouldWrap };
        },
        template: `
        <OptionalWrapper class="tw-text-center tw-bg-violet-200 tw-p-2" :shouldWrap="shouldWrap">
            <p>
                I feel fancy when I'm wrapped
                <br/><br/>
                <i>toggle the shouldWrap in the Docs tab</i>
            </p>   
        </OptionalWrapper>
        `,
    };
};

export const Default = Template.bind({});

export default story;
