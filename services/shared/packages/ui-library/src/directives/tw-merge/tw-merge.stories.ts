import type { Meta, StoryFn } from '@storybook/vue';
import { defineComponent } from 'vue';
import duplicateClassImage from './tailwind-duplicate-classes.png';

interface StoryProps {
    classes: string;
    enableTwMerge: boolean;
}

const story: Meta = {
    title: 'Directives/v-tw-merge',
    parameters: {
        docs: {
            description: {
                component: `The \`v-tw-merge\` directive applies the [tailwind merge](https://www.npmjs.com/package/tailwind-merge) functionality
                 to the element's parent inherited classes. This is useful because it allows you to override any default styles that have been set in the initial component definition.
                <br/>
                <br/>
                For example: there is a \`<Something />\` component which in its implementation has the utility class \`tw-w-full\` applied. Normally when you use
                 this component in your template and would like to change the width using a tailwind utility class (e.g. \`tw-w-1/2\`). It might not work since it is dependend
                 on which class is defined last in the Tailwind CSS. Using this directive it will always ensure that classes that are applied up in the parent inheritance line will
                 overwrite previously defined tailwind classes.
                <br/>
                <br/>
                __Duplicate classes inside a component definition__
                This directive is meant to merge tailwind classes that are defined upwards in the component inheritance line. Meaning it will merge the tailwind classes
                 defined in the definition of \`Component A\` with classes that are defined on the usage of \`Component A\` in a template somewhere.
                <br/>
                <br/>
                This directive __will not merge duplicate tailwind classes that are defined in the initial implementation itself__. (For example \`class="tw-text-sm tw-text-lg"\`.)
                 This is not something you should be doing - and you should take care of yourself. If you have
                  the [Tailwind CSS extension](https://marketplace.visualstudio.com/items?itemName=bradlc.vscode-tailwindcss) installed in VSCode it will also warn you about this. 
                <br/>
                <br/>
                <img src="${duplicateClassImage}"/>
                <br/>
                <br/>
                <br/>
                To try it out, apply some included classes such as \`tw-bg-green-200\` and see how \`v-tw-merge\` takes care of the tailwind class merge
                `,
            },
        },
    },
    args: {
        classes: 'tw-bg-green-300 tw-text-lg',
        enableTwMerge: true,
    },
    argTypes: {
        classes: {
            control: 'text',
        },
        enableTwMerge: { control: 'boolean' },
    },
};

const ExampleComponent = defineComponent({
    props: {
        enableTwMerge: Boolean,
    },
    setup({ enableTwMerge }) {
        return { enableTwMerge };
    },
    template: `
        <div v-if="enableTwMerge" v-tw-merge class="tw-bg-violet-300 tw-p-4 tw-my-8 tw-font-sans tw-text-sm"><slot/></div>
        <div v-else class="tw-bg-violet-300 tw-p-4 tw-my-8 tw-font-sans tw-text-sm"><slot/></div>
    `,
});

const Template: StoryFn<StoryProps> = ({ classes, enableTwMerge }) => {
    return {
        components: { ExampleComponent },
        setup() {
            return { classes, enableTwMerge };
        },
        template: `
            <div>
                <ExampleComponent :enableTwMerge="enableTwMerge" :class="classes">
                    Hello world
                </ExampleComponent>
                <i class="tw-body-sm tw-font-sans">Note that the class you wish to apply needs to be referenced in a story somewhere for it to be compiled into the CSS and thus available.</i>
            </div>
            `,
    };
};

export const Default = Template.bind({});

export default story;
