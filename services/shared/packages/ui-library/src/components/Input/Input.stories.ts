import { action } from '@storybook/addon-actions';
import type { Meta } from '@storybook/vue';
import Input from './Input.vue';
import { setupStory } from '../../../docs/utils/story';
import { HStack, VStack } from '../..';

const onChange = action('change');
const onInput = action('input');

const types = ['text', 'tel', 'email', 'password', 'number', 'date'];

export default {
    title: 'Components/Input',
    component: Input,
    args: {
        name: 'name',
    },
    argTypes: {
        name: { control: 'text' },
        type: { control: { type: 'select' }, options: types },
        disabled: { control: { type: 'boolean' } },
    },
} as Meta;

export const Default = setupStory({
    components: { Input },
    props: { onChange, onInput },
    template: `
            <Input 
                v-bind="args" 
                @change="onChange" 
                @input="onInput" 
            />
    `,
});

export const States = setupStory({
    components: { Input, VStack, HStack },
    props: { onChange, onInput, states: ['default', 'required', 'disabled', 'readonly'] },
    template: `
    <HStack spacing="8">
        <VStack class="tw-items-start">
            <Input 
                v-bind="args" 
                v-for="state in states"  
                @change="onChange" 
                @input="onInput" 
                :value="state"
                :required="state === 'required'"
                :disabled="state === 'disabled'"
                :readonly="state === 'readonly'"
            />
        </VStack>
        <VStack>
            <Input 
                v-bind="args" 
                v-for="state in states"  
                @change="onChange" 
                @input="onInput" 
                invalid
                :value="\`\${state} invalid\`" 
                :required="state === 'required'"
                :disabled="state === 'disabled'"
                :readonly="state === 'readonly'"
            />
        </VStack>
    </HStack spacing="8">
    `,
});
