import { action } from '@storybook/addon-actions';
import type { Meta } from '@storybook/vue';
import { ref } from 'vue';
import { HStack, VStack } from '../..';
import { markdown } from '../../../docs/utils';
import { setupStory } from '../../../docs/utils/story';
import Select from './Select.vue';

const variants = ['outline', 'plain'];
const sizes = ['sm', 'md', 'lg'];

const onBlur = action('blur');
const onChange = action('change');

export default {
    title: 'Components/Select',
    component: Select,
    args: {
        name: 'name',
    },
    argTypes: {
        name: { control: 'text' },
        disabled: { control: { type: 'boolean' } },
        variant: { control: { type: 'select' }, options: variants },
        size: { control: { type: 'select' }, options: sizes },
    },
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?type=design&node-id=491-2467&t=s3bh0Anbd8Vgyiig-0',
        },
    },
} as Meta;

export const Default = setupStory({
    components: { Select, VStack },
    setup() {
        const value = ref('option1');

        function handleChange(event: ChangeEvent<HTMLSelectElement>) {
            value.value = event.target.value;
        }

        return { value, handleChange };
    },
    template: `
    <VStack class="tw-items-start">
        Current value: {{ value }}
        <Select 
                v-bind="args" 
                @blur="onBlur" 
                @change="handleChange" 
            >
            <option value='option1' :checked="value === 'option1'">Option 1</option>
            <option value='option2' :checked="value === 'option2'">Option 2</option>
            <option value='option3' :checked="value === 'option3'">Option 3</option>
        </Select>
    <VStack>
`,
    props: { onBlur, onChange },
});

export const States = setupStory({
    components: { Select, VStack, HStack },
    props: { onBlur, onChange, states: ['default', 'required', 'disabled', 'readonly'] },
    template: `
    <HStack spacing="8">
        <VStack class="tw-items-start">
            <Select 
                    v-bind="args" 
                    v-for="state in states" 
                    @blur="onBlur" 
                    @change="onChange" 
                    :placeholder="state" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                >
                <option value='option1'>Option 1</option>
                <option value='option2'>Option 2</option>
                <option value='option3'>Option 3</option>
            </Select>
        </VStack>
        <VStack class="tw-items-start">
            <Select 
                    v-bind="args" 
                    v-for="state in states" 
                    @blur="onBlur" 
                    @change="onChange" 
                    invalid
                    :placeholder="\`\${state} invalid\`" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                >
                <option value='option1'>Option 1</option>
                <option value='option2'>Option 2</option>
                <option value='option3'>Option 3</option>
            </Select>
        </VStack>
    </HStack>`,
});

export const LongContent = setupStory({
    components: { Select, VStack },
    props: { onBlur },
    template: `
    <VStack class="tw-items-start">
        <Select v-bind="args" @blur="onBlur" placeholder="Modi velit nulla eaque blanditiis mollitia veniam ex ut quaerat.">
            <option value='option1'>Exercitationem placeat repellat sapiente id incidunt placeat alias odit.</option>
            <option value='option2'>Dolore tenetur repellendus qui non voluptatibus beatae aliquid quas culpa iure quasi. Quibusdam quasi sunt nesciunt animi tenetur aspernatur porro facere.</option>
            <option value='option3'>Iste et excepturi itaque quidem vel similique saepe temporibus deleniti quod ratione molestiae doloribus. Aut nesciunt exercitationem fugit explicabo sint deserunt excepturi doloribus rerum omnis eum. Deserunt maiores quisquam neque vero aliquid libero placeat ipsum molestiae perspiciatis. Nulla atque maiores numquam voluptas magni ab magni dolor occaecati in vel explicabo architecto voluptates. Eius maiores officiis laborum ad quia aut perferendis recusandae delectus ut.</option>
        </Select>
    </VStack>`,
});

export const VariantsAndSizes = setupStory({
    components: { Select, VStack, HStack },
    description: `There are several variants (${markdown.toCodeString(variants)}) and sizes (${markdown.toCodeString(
        sizes
    )}) to choose from.`,
    props: {
        states: ['default', 'required', 'disabled', 'readonly'],
        sizes,
        variants,
    },
    template: `
    <VStack>
        <VStack v-for='variant in variants' :key="variant">
            <HStack v-for='state in states' :key="state">
                <Select 
                    v-for='size in sizes' 
                    :key="size" 
                    v-bind="args" 
                    :size="size" 
                    :variant="variant" 
                    :required="state === 'required'"
                    :disabled="state === 'disabled'"
                    :readonly="state === 'readonly'"
                    :placeholder="\`\${variant} \${size} \${state}\`"
                />
            </HStack>
        </VStack>
    </VStack>
    `,
});
