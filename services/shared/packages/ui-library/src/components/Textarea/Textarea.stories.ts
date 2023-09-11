import { action } from '@storybook/addon-actions';
import type { Meta } from '@storybook/vue';
import Textarea from './Textarea.vue';
import { setupStory } from '../../../docs/utils/story';
import { VStack } from '../..';

const onChange = action('change');
const onInput = action('input');

export default {
    title: 'Components/Textarea',
    component: Textarea,
    args: {
        name: 'name',
    },
    argTypes: {
        name: { control: 'text' },
    },
} as Meta;

export const Default = setupStory({
    components: { Textarea, VStack },
    template: `
    <VStack>
        <Textarea v-bind="args" @change="onChange" @input="onInput" value="regular" />
        <Textarea v-bind="args" required @change="onChange" @input="onInput" value="required"/>
        <Textarea v-bind="args" disabled @change="onChange" @input="onInput" value="disabled"/>
        <Textarea v-bind="args" readonly @change="onChange" @input="onInput" value="readonly"/>
    </VStack>`,
    props: { onChange, onInput },
});
