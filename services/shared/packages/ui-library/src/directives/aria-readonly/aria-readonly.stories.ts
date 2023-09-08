import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../docs/utils/story';
import { ref } from 'vue';
import { Button, VStack } from '../..';

const story: Meta = {
    title: 'Directives/v-aria-readonly',
    parameters: {
        docs: {
            description: {
                component: `The \`v-aria-readonly\` is a directive to enable a read-only state on an element. 
                This is useful for elements that do not natively support read-only, such as \`<input type="radio">\` or \`<select>\`.
                It assigns the ARIA attribute \`aria-readonly="true"\` to the element. And also prevents the user from changing the value.
                `,
            },
        },
    },
};

export const Default = setupStory({
    components: { VStack, Button },
    setup() {
        const readOnly = ref(false);
        function toggleReadOnly() {
            readOnly.value = !readOnly.value;
        }
        return { readOnly, toggleReadOnly };
    },
    template: `
    <VStack class="tw-items-start">
        <Button size="sm" @click="toggleReadOnly">{{readOnly ? 'Disable read-only' : 'Enable read-only'}}</Button>
    
        <select v-aria-readonly="readOnly">
            <option value="">Select an option</option>
            <option value="1">Option 1</option>
            <option value="2">Option 2</option>
            <option value="3">Option 3</option>
        </select>
    </VStack>
    `,
});

export default story;
