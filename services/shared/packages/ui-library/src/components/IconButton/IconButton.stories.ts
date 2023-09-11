import type { Meta } from '@storybook/vue';
import IconButton from './IconButton.vue';
import { action } from '@storybook/addon-actions';
import buttonStories from '../Button/Button.stories';

import { setupStory } from '../../../docs/utils/story';
import { HStack } from '../..';

const { target, iconLeft, type, size, variant, color, disabled, loading } = buttonStories.argTypes;
const onClick = action('click');

export default {
    title: 'Components/IconButton',
    component: IconButton,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?node-id=249%3A281',
        },
        docs: {
            description: {
                component:
                    'The `IconButton` component is a button that only contains an icon. It can be used to trigger actions or navigate to a new page.<br/><br/>The `IconButton` is build on top of the regular `Button` and therefore shares a lot of the same properties.<br/>Note that the `aria-label` is required for `IconButton`.',
            },
        },
    },
    args: {
        icon: 'chevron-right',
        ariaLabel: 'Odio in quidem voluptatibus facilis quam vel voluptatem doloremque quia vel odio.',
    },
    argTypes: {
        target,
        icon: iconLeft,
        type,
        size,
        variant,
        color,
        disabled,
        loading,
    },
} as Meta;

export const Default = setupStory({
    components: { IconButton },
    template: `<IconButton v-bind="args" @click="onClick" />`,
    props: { onClick },
});

export const IconButtonAsLink = setupStory({
    components: { IconButton, HStack },
    description: `
IconButtons will render as an \`a\` tag when an \`href\` is provided. When the button is rendered as an \`a\` tag the same rules apply as to a regular \`Link\`:
- Outgoing links (\`target="_blank"\`) will have \`rel="noopener"\` by default.
`,
    template: `
    <HStack>
        <IconButton v-bind="args" href="http://google.com" target="_blank" ariaLabel="Link"/>
        <IconButton v-bind="args" href="http://google.com" target="_blank" ariaLabel="Disabled Link" disabled />
    </HStack>

    `,
});

export const Variants = setupStory({
    components: { IconButton },
    props: {
        states: ['regular', 'disabled'],
        colors: color.options,
        sizes: size.options,
        variants: variant.options,
    },
    template: `
    <div>
        <div v-for='color in colors' :key="color">
            <div v-for='state in states' :key="state" class='tw-mb-4 tw-mr-8 tw-inline-block'>
                <div v-for='variant in variants' :key="variant" class='tw-mb-4'>
                    <IconButton
                        v-for='size in sizes'
                        v-bind="args"
                        :key="size"
                        :size="size"
                        :variant="variant"
                        :color="color"
                        :disabled="state === 'disabled'"
                        class="tw-mr-4"
                    />
                </div>
            </div>
        </div>
    </div>
    `,
});

export const VariantsWithLoading = setupStory({
    components: { IconButton },
    props: {
        colors: color.options,
        sizes: size.options,
        variants: variant.options,
    },
    template: `
    <div>
        <div v-for='color in colors' :key="color">
            <div v-for='variant in variants' :key="variant" class='tw-mb-4'>
                <IconButton
                    v-for='size in sizes'
                    :key="size"
                    v-bind="args"
                    :size="size"
                    :variant="variant"
                    :color="color"
                    loading
                    class="tw-mr-4"
                />
            </div>
        </div>
    </div>
    `,
});
