import ButtonOrLink from './ButtonOrLink.vue';
import { setupStory } from '../../../docs/utils/story';

export default {
    title: 'Components/ButtonOrLink',
    component: ButtonOrLink,
    parameters: {
        docs: {
            description: {
                component: `
The \`ButtonOrLink\` is an abstract component not meant to be used directly, but rather as a base implementation for other buttons or links.
Which is also why it is not exported from the \`ui-library\`.
It enables the same functionality for buttons and links such as a \`click\` listener, an \`href\` or a \`to\` prop for \`vue-router\`.
It will render as a \`<button>\`, \`<a>\` or \`<router-link>\` accordingly. It can also render as a \`<span>\` in case of a link that is disabled.

> __Note: You can not use both \`to\` and \`href\` at the same time. If you do, the \`href\` will be ignored.__


\`\`\`html
<ButtonOrLink @click="handleClick"> // renders as <button>
<ButtonOrLink href="https://..."> // renders as <a>
<ButtonOrLink to="/someroute/second"> // renders as <router-link>
...
<ButtonOrLink href="https://..." disabled> // renders as <span>
\`\`\`

> #### \`[Vue warn]: Unknown custom element: <router-link>\`
> Note: the \`router-link\` component is not available in storybook as the \`vue-router\` is not included in the ui-library package. Therefore you will get some Vue warnings when using the \`to\` property in a story.
`,
            },
        },
    },
    args: {
        content: 'Label',
    },
    argTypes: {
        to: { control: 'text' },
        href: { control: 'text' },
        rel: { control: 'text' },
        type: { control: { type: 'select' }, options: ['button', 'submit', 'reset'] },
        target: { control: { type: 'select' }, options: ['_blank', '_self', '_parent', '_top'] },
        disabled: { control: 'boolean' },
        ariaLabel: { control: 'text' },
        content: { control: 'text' },
    },
} as const;

export const Default = setupStory({
    components: { ButtonOrLink },
    template: `<ButtonOrLink v-bind="args">{{args.content}}</ButtonOrLink>`,
});
