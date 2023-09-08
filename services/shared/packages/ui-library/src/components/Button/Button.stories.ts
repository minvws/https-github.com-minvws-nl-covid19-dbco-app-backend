import Button from './Button.vue';
import { HStack } from '../../';
import { action } from '@storybook/addon-actions';
import { iconNames } from '../Icon/icons';
import { markdown } from '../../../docs/utils';
import { setupStory } from '../../../docs/utils/story';
import buttonOrLinkStories from '../ButtonOrLink/ButtonOrLink.stories';

const variants = ['solid', 'outline', 'plain'];
const colors = ['violet', 'red'];
const sizes = ['sm', 'md', 'lg'];
const loadingPositions = ['left', 'right'];
const onClick = action('click');

export default {
    title: 'Components/Button',
    component: Button,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?node-id=249%3A281',
        },
        docs: {
            description: {
                component:
                    'Button component is used to trigger an action or event, such as submitting a form, opening a Dialog, canceling an action, or performing a delete operation.',
            },
        },
    },
    args: {
        ...buttonOrLinkStories.args,
    },
    argTypes: {
        ...buttonOrLinkStories.argTypes,
        variant: { control: { type: 'select' }, options: variants },
        color: { control: { type: 'select' }, options: colors },
        size: { control: { type: 'select' }, options: sizes },
        iconLeft: { control: { type: 'select' }, options: iconNames },
        iconRight: { control: { type: 'select' }, options: iconNames },
        loading: { control: 'boolean' },
        loadingPosition: { control: { type: 'select' }, options: loadingPositions },
    },
} as const;

export const Default = setupStory({
    components: { Button },
    template: `<Button v-bind="args" @click="onClick">{{args.content}}</Button>`,
    props: { onClick },
});

export const ButtonAsLink = setupStory({
    components: { Button, HStack },
    description: `
Buttons will render as an \`a\` tag when an \`href\` is provided. When the button is rendered as an \`a\` tag the same rules apply as to a regular \`Link\`:
- Outgoing links (\`target="_blank"\`) will have \`rel="noopener"\` by default.
- Outgoing links (\`target="_blank"\`) will log a warning if \`aria-label\` has not been set.
`,
    template: `
    <HStack>
        <Button v-bind="args" href="http://google.com" target="_blank" aria-label="open link">Link</Button>
        <Button v-bind="args" href="http://google.com" target="_blank" aria-label="open link" disabled>Disabled Link</Button>
    </HStack>
    
    `,
});

export const ButtonWithFullWidth = setupStory({
    components: { Button },
    description: 'To have a button expand the full width, simply apply the `tw-w-full` class.',
    props: {
        iconPositions: ['none', 'left', 'right'],
        variants,
    },
    template: `
    <div>
        <div v-for='iconPosition in iconPositions' :key="iconPosition" class='tw-mb-4 tw-block'>
            <div v-for='variant in variants' :key="variant" class="tw-mb-4">
                <Button 
                    v-bind="args" 
                    :variant="variant" 
                    :iconLeft="iconPosition === 'left' ? 'chevron-left' : null" 
                    :iconRight="iconPosition === 'right' ? 'chevron-right' : null" 
                    class="tw-w-full"
                >{{args.content}}</Button>
            </div>
        </div>
    </div>
    `,
});

export const ButtonOverflow = setupStory({
    components: { Button },
    props: {
        iconPositions: ['none', 'left', 'right'],
        variants,
    },
    template: `
    <div>
        <div v-for='iconPosition in iconPositions' :key="iconPosition" class='tw-mb-4 tw-mr-8 tw-inline-block tw-align-top'>
            <div v-for='variant in variants' :key="variant" class="tw-mb-4">
                <Button 
                    v-bind="args" 
                    :variant="variant" 
                    :iconLeft="iconPosition === 'left' ? 'chevron-left' : null" 
                    :iconRight="iconPosition === 'right' ? 'chevron-right' : null" 
                    class="tw-mr-4 tw-max-w-[200px]"
                >This is a button with a very long label. Something we try to avoid, of course.</Button>
            </div>
        </div>
    </div>
    `,
});

export const Variants = setupStory({
    components: { Button },
    description: `There are several variants (${markdown.toCodeString(variants)}) and colors  (${markdown.toCodeString(
        colors
    )}) to choose from.`,
    props: {
        states: ['regular', 'disabled'],
        colors,
        sizes,
        variants,
    },
    template: `
    <div>
        <div v-for='color in colors' :key="color">
            <div v-for='state in states' :key="state" class='tw-mb-4 tw-mr-8 tw-inline-block'>
                <div v-for='variant in variants' :key="variant" class='tw-mb-4'>
                    <Button 
                        v-for='size in sizes' 
                        :key="size" 
                        v-bind="args" 
                        :size="size" 
                        :variant="variant" 
                        :color="color" 
                        :disabled="state === 'disabled'"
                        class="tw-mr-4"
                    >{{args.content}}</Button>
                </div>
            </div>
        </div>
    </div>
    `,
});

export const VariantsWithLoading = setupStory({
    components: { Button },
    description:
        'The loading spinner can be on the left side or right side. Also the loading text can be optionally set.',
    props: {
        loadingPositions,
        colors,
        sizes,
        variants,
    },
    template: `
    <div>
        <div v-for='color in colors' :key="color">
            <div v-for='loadingPosition in loadingPositions' :key="loadingPosition" class='tw-mb-4 tw-mr-8 tw-inline-block'>
                <div v-for='variant in variants' :key="variant" class='tw-mb-4'>
                    <Button 
                        v-for='size in sizes' 
                        :key="size" 
                        v-bind="args" 
                        :size="size" 
                        :variant="variant" 
                        :color="color" 
                        loading
                        :loadingText="loadingPosition === 'right' ? 'loading...' : null"
                        :loadingPosition="loadingPosition"
                        class="tw-mr-4"
                    >{{args.content}}</Button>
                </div>
            </div>
        </div>
    </div>
    `,
});

export const VariantsWithIcons = setupStory({
    components: { Button },
    description:
        'Buttons can have an icon on the left side or right side. To see all available Icons, see the `Icon` component.',
    props: {
        iconPositions: ['left', 'right'],
        colors,
        sizes,
        variants,
    },
    template: `
    <div>
        <div v-for='color in colors' :key="color">
            <div v-for='iconPosition in iconPositions' :key="iconPosition" class='tw-mb-4 tw-mr-8 tw-inline-block'>
                <div v-for='variant in variants' :key="variant" class="tw-mb-4">
                    <Button 
                        v-for='size in sizes' 
                        :key="size" 
                        v-bind="args" 
                        :size="size" 
                        :variant="variant" 
                        :color="color"
                        :iconLeft="iconPosition === 'left' ? 'chevron-left' : null" 
                        :iconRight="iconPosition === 'right' ? 'chevron-right' : null" 
                        class="tw-mr-4"
                    >{{args.content}}</Button>
                </div>
            </div>
        </div>
    </div>
    `,
});
