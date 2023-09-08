import { HStack, VStack } from '../..';
import Radio from './Radio.vue';
import RadioGroup from './RadioGroup.vue';
import { setupStory } from '../../../docs/utils/story';
import { action } from '@storybook/addon-actions';
import { ref } from 'vue';

const onChange = action('change');
const variants = ['plain', 'button', 'switch'];

export default {
    title: 'Components/Radio',
    component: Radio,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?type=design&node-id=529-3153&t=bjK8a2U5NQQZY1lf-0',
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
        variant: { control: 'select', options: variants },
    },
} as const;

export const Default = setupStory({
    components: { Radio },
    props: { onChange },
    template: `
    <Radio 
        v-bind="args" 
        @change="onChange"
    >
        I agree to all the terms and conditions
    </Radio>
    `,
});
export const VariantsAndStates = setupStory({
    components: { Radio, VStack, HStack },
    props: { onChange, variants, states: ['default', 'required', 'disabled', 'readonly'] },
    template: `
    <VStack spacing="8">
        <HStack spacing="8" v-for="variant in variants" :key="variant" v-if="variant !== 'switch'">
            <VStack>
                <Radio 
                    v-bind="args" 
                    v-for="state in states"
                    :key="state"
                    @change="onChange"
                    :variant="variant"
                    name="variant" 
                    :value="state"
                    :id="state"
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                >
                    {{ variant }} / {{ state }}
                </Radio>
            </VStack>
            <VStack>
                <Radio 
                    v-bind="args" 
                    v-for="state in states"
                    :key="state"
                    @change="onChange"
                    :variant="variant"
                    checked
                    :name="\`\${variant} \${state} checked\`" 
                    :id="\`\${state} checked\`" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                >
                    {{ variant }} / {{ state }} / checked
                </Radio>
            </VStack>
            <VStack>
                <Radio 
                    v-bind="args" 
                    v-for="state in states"
                    :key="state"
                    @change="onChange"
                    :variant="variant"
                    invalid
                    name="variant"
                    :value="\`\${state} invalid\`"
                    :id="\`\${state} invalid\`" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                >
                    {{ variant }} / {{ state }}  / invalid
                </Radio>
            </VStack>
        </HStack>
    </VStack>`,
});

export const RadioGroupExample = setupStory({
    components: { RadioGroup, Radio, VStack, HStack },
    props: {
        onChange,
        variants,
    },
    setup() {
        const value = ref('1');

        function handleChange(event: ChangeEvent<HTMLInputElement>) {
            value.value = event.target.value;
        }

        return { value, handleChange };
    },
    template: `
    <VStack>
        Current value: {{ value }}
        <HStack spacing="8">
            <RadioGroup v-for="variant in variants" :key="variant" :variant="variant" :value="value" @change="handleChange" v-if="variant !== 'switch'">
                <VStack>
                    <Radio value="1">Option 1</Radio>
                    <Radio value="2">Option 2</Radio>
                    <Radio value="3">Option 3</Radio>
                </VStack>
            </RadioGroup>
        </HStack>
    </VStack>
    `,
});

export const RadioGroupSwitchExample = setupStory({
    components: { RadioGroup, Radio, VStack, HStack },
    props: {
        onChange,
        variants,
    },
    setup() {
        const value = ref('false');

        function handleChange(event: ChangeEvent<HTMLInputElement>) {
            value.value = event.target.value;
        }

        return { value, handleChange };
    },
    template: `
    <VStack>
        Current value: {{ value }}
        <RadioGroup class="tw-flex" variant="switch" :value="value" @change="handleChange">
            <Radio value="true">Option 1</Radio>
            <Radio value="false">Option 2</Radio>
        </RadioGroup>
    </VStack>
    `,
});
