import type { Meta } from '@storybook/vue';
import { HStack, VStack } from '../..';
import { setupStory } from '../../../docs/utils/story';
import FormElementOutline from './FormElementOutline.vue';

export default {
    title: 'Components/FormElementOutline',
    component: FormElementOutline,
    parameters: {
        docs: {
            description: {
                component: `The \`FormElementOutline\` is used to wrap form components and provide consistent styling for the outline and disabled / readonly states.
                It provides basic styles that can be used for a form component that are made available via the slot props. 
                It also adds a focus glow around the wrapped component. <br><br>
                It is currently used (among others) for the \`Input\`, \`Textarea\`, \`Select\` and \`Radio\` components.
                `,
            },
        },
    },
    argTypes: {
        invalid: { control: { type: 'boolean' } },
        useFocusOutline: { control: { type: 'boolean' } },
        round: { control: { type: 'boolean' } },
        square: { control: { type: 'boolean' } },
    },
} as Meta;

export const Default = setupStory({
    components: { FormElementOutline },
    template: `
        <FormElementOutline v-bind="args" v-slot="slotProps">
            <input
                value="regular"
                type="text"
                :class="['tw-form-input', ...slotProps.styles]"
            />
        </FormElementOutline>
`,
});

export const States = setupStory({
    components: { FormElementOutline, VStack, HStack },
    props: { states: ['default', 'required', 'disabled', 'readonly'] },
    template: `
    <HStack spacing="8">
        <VStack>
            <FormElementOutline v-bind="args" v-slot="slotProps" v-for="state in states">
                <input
                    :class="['tw-form-input', ...slotProps.styles]"
                    type="text"
                    :value="state"
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                />
            </FormElementOutline>
        </VStack>
        <VStack>
            <FormElementOutline v-bind="args" v-slot="slotProps" v-for="state in states" invalid>
                <input
                    :class="['tw-form-input', ...slotProps.styles]"
                    type="text"
                    :value="\`\${state} invalid\`" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                />
            </FormElementOutline>
        </VStack>
    </HStack>
    `,
});
