import type { Meta } from '@storybook/vue';
import Alert from './Alert.vue';
import { setupStory } from '../../../docs/utils/story';
import { VStack } from '../Stack';

const asValues = ['div', 'section', 'article'] as const;
const variants = ['info', 'success', 'warning', 'error'] as const;

const story: Meta = {
    title: 'Components/Alert',
    component: Alert,
    parameters: {
        docs: {
            description: {
                component: 'A Card component for displaying content such as events.',
            },
        },
    },
    argTypes: {
        as: { options: asValues, control: { type: 'select' }, default: 'div' },
        variant: { options: variants, control: { type: 'select' } },
    },
};

export const Default = setupStory({
    components: { Alert },
    template: `
    <Alert v-bind="args">
        Accusantium quibusdam quisquam delectus dolorum ab a sed suscipit quo distinctio ducimus facere.
    </Alert>
    `,
});

export const Variants = setupStory({
    components: { VStack, Alert },
    props: { variants },
    template: `
    <VStack>
        <Alert v-for="variant in variants" v-bind="args" :variant="variant">
            {{ variant }}
        </Alert>
    </VStack>
    `,
});

export const VariantsWithAdditional = setupStory({
    components: { VStack, Alert },
    props: { variants },
    template: `
    <VStack>
        <Alert v-for="variant in variants" v-bind="args" :variant="variant" :key="variant">
            {{ variant }}
            <template #additional>
                Dolorem qui deleniti nemo dolore ad error tenetur quae odit adipisci. Est tempore animi dolores perspiciatis numquam ab perferendis quod earum consequuntur.
            </template>
        </Alert>
    </VStack>
    `,
});

export default story;
