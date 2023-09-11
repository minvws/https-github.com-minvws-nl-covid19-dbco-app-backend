import type { Meta } from '@storybook/vue';
import Link from './Link.vue';
import { action } from '@storybook/addon-actions';
import { iconNames } from '../Icon/icons';
import { markdown } from '../../../docs/utils';
import { setupStory } from '../../../docs/utils/story';
import buttonOrLinkStories from '../ButtonOrLink/ButtonOrLink.stories';

const variants = ['plain', 'underlined'];
const sizes = ['sm', 'md', 'lg'];
const onClick = action('click');

export default {
    title: 'Components/Link',
    component: Link,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?node-id=262%3A686',
        },
        docs: {
            description: {
                component: `
Link is an accessible element for navigation but can also be used to trigger actions. It will render as an \`<a>\` or \`<button>\` tag, depending whether an href has been set.
- When no \`href\` is set, it will render as a \`<button>\` element.
- Outgoing links (\`target="_blank"\`) will have \`rel="noopener"\` by default.
- Outgoing links (\`target="_blank"\`) will log a warning if \`aria-label\` has not been set.
`,
            },
        },
    },
    args: {
        ...buttonOrLinkStories.args,
    },
    argTypes: {
        ...buttonOrLinkStories.argTypes,
        variant: { control: { type: 'select' }, options: variants },
        size: { control: { type: 'select' }, options: sizes },
        iconLeft: { control: { type: 'select' }, options: iconNames },
        iconRight: { control: { type: 'select' }, options: iconNames },
    },
} as Meta;

export const Default = setupStory({
    components: { Link },
    template: `<Link v-bind="args">{{args.content}}</Link>`,
});

export const Variants = setupStory({
    components: { Link },
    description: `There are several variants (${markdown.toCodeString(variants)}) to choose from.`,
    props: {
        variants,
        disabled: [false, true],
    },
    template: `
    <div>
        <div v-for='variant in variants' :key="variant">
            <Link 
            v-for='disable in disabled'
            v-bind="args"
            href="#url"
            :key="disable" 
            :variant="variant" 
            :disabled="disable"
            class="tw-mr-4"
            >{{\`\${args.content} \${disable ? ' disabled' : ''}\`}}</Link>
        </div>
    </div>
    `,
});

export const LinkAsAButton = setupStory({
    components: { Link },
    description: `A \`Link\` will render as a \`<button>\` when no \`href\` is set. This is useful when attaching \`click\` listeners to trigger an action. As there will most likely be no meaningful \`href\` value, a \`button\` element is more appropriate.`,
    props: { onClick },
    template: `<Link v-bind="args" @click="onClick">{{args.content}}</Link>`,
});

export const ExternalLinks = setupStory({
    components: { Link },
    description: `
#### Screen reader accessibility
- When a link receives focus, screen readers should announce a descriptive link name. If the link opens in a new window or browser tab, add an \`aria-label\` to inform screen reader users—for example, "To learn more, visit the About page which opens in a new window."
`,
    template: `<Link v-bind="args" target="_blank" href="https://www.google.com/" aria-label="Continue to Google, this will open in a new window." iconLeft="external-link">{{args.content}}</Link>`,
});

export const LinkInlineWithText = setupStory({
    components: { Link },
    description: `
Links can also be used inline within text.
- __When using \`Link\` inside text, \`variant="underlined"\` should be used.__
    `,
    template: `
    <div>
        <p class="tw-body-md">
            <strong>Perferendis libero est nihil velit quas at <Link href="#bold" variant="underlined">beatae delectus.</Link></strong> Itaque architecto accusamus 
            architecto voluptatem dolores hic repudiandae enim assumenda laudantium quisquam adipisci nulla. 
            Doloribus cum adipisci ullam tempora ab deleniti aperiam ipsam porro beatae. Ut harum et dolores nihil 
            <Link v-bind="args" href="#url" variant="underlined">{{args.content}}</Link> sit cupiditate quaerat animi repudiandae 
            blanditiis assumenda dolor. Itaque deleniti hic tempore quis sequi sunt est.
        </p>
    </div>
    `,
});

export const SizesAndIcons = setupStory({
    components: { Link },
    description: `There are several sizes (${markdown.toCodeString(
        sizes
    )}) to choose from. If no size is set, __it will inherit the font size from its parent by default__. An optional icon can be places on the left or right.`,
    props: {
        iconPositions: ['none', 'left', 'right'],
        variants,
        sizes,
    },
    template: `
    <div>
        <div v-for='variant in variants' :key="variant" class="tw-mb-8">
            <div v-for='size in sizes' :key="size" class="tw-flex tw-flex-row tw-gap-8 tw-mb-4">
                <div v-for='iconPosition in iconPositions' :key="iconPosition">
                    <Link 
                    v-bind="args" 
                    :variant="variant" 
                    :size="size" 
                    :iconLeft="iconPosition === 'left' ? 'chevron-left' : undefined" 
                    :iconRight="iconPosition === 'right' ? 'chevron-right' : undefined" 
                    >{{args.content}}</Link>
                </div>
            </div>
        </div>
    </div>
    `,
});

export const LinkOverflow = setupStory({
    components: { Link },
    props: {
        iconPositions: ['none', 'left', 'right'],
        variants,
    },
    template: `
    <div>
        <div v-for='variant in variants' :key="variant" class="tw-mb-4 tw-flex tw-items-center">
            <div v-for='iconPosition in iconPositions' :key="iconPosition" class='tw-mb-4 tw-mr-8 tw-inline-block tw-align-top'>
                <Link 
                    v-bind="args" 
                    :variant="variant" 
                    :iconLeft="iconPosition === 'left' ? 'chevron-left' : null" 
                    :iconRight="iconPosition === 'right' ? 'chevron-right' : null" 
                    class="tw-mr-4 tw-max-w-[200px]"
                >This is a button with a very long label. Something we try to avoid, of course.</Link>
            </div>
        </div>
    </div>
    `,
});
