import type { Meta } from '@storybook/vue';
import { HStack, VStack } from '../..';
import Checkbox from './Checkbox.vue';
import { setupStory } from '../../../docs/utils/story';
import { action } from '@storybook/addon-actions';

const onChange = action('change');

export default {
    title: 'Components/Checkbox',
    component: Checkbox,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/🎨-Components?node-id=514-3830&t=Es7rEFSuRY2XEIs4-0',
        },
    },
    args: {
        label: 'Label',
        id: 'unique-id',
        value: 'value',
        ariaLabel: 'An ARIA label',
        disabled: false,
        checked: false,
    },
    argTypes: {
        id: { control: 'text' },
        value: { control: 'text' },
        label: { control: 'text' },
        ariaLabel: { control: 'text' },
        disabled: { control: 'boolean' },
        checked: { control: 'boolean' },
    },
} as Meta;

export const Default = setupStory({
    components: { Checkbox },
    props: { onChange },
    template: `
    <Checkbox 
        v-bind="args" 
        @change="onChange"
    >
        I agree to all the terms and conditions
    </Checkbox>
    `,
});
export const States = setupStory({
    components: { Checkbox, VStack, HStack },
    props: { onChange, states: ['default', 'required', 'disabled', 'readonly'] },
    template: `
    <HStack spacing="8">
        <VStack>
            <Checkbox 
                v-bind="args" 
                v-for="state in states"
                @change="onChange"
                :id="state"
                :name="state" 
                :required="state === 'required'"
                :disabled="state === 'disabled'"
                :readonly="state === 'readonly'"
            >
                {{ state }}
            </Checkbox>
        </VStack>
        <VStack>
            <Checkbox 
                v-bind="args" 
                v-for="state in states"
                @change="onChange"
                checked
                :id="\`\${state} checked\`" 
                :name="\`\${state} checked\`" 
                :required="state === 'required'"
                :disabled="state === 'disabled'"
                :readonly="state === 'readonly'"
            >
                {{ state }} checked
            </Checkbox>
        </VStack>
        <VStack>
            <Checkbox 
                v-bind="args" 
                v-for="state in states"
                @change="onChange"
                invalid
                :id="\`\${state} invalid\`" 
                :name="\`\${state} invalid\`" 
                :required="state === 'required'"
                :disabled="state === 'disabled'"
                :readonly="state === 'readonly'"
            >
                {{ state }} invalid
            </Checkbox>
        </VStack>
    </HStack>
    `,
});
