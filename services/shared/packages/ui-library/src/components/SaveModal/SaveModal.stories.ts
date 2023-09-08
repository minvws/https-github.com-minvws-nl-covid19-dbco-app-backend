import type { Meta } from '@storybook/vue';
import Button from '../Button/Button.vue';
import SaveModal from './SaveModal.vue';
import { setupStory } from '../../../docs/utils/story';
import { useOpenState } from '../../composables';

export default {
    title: 'Components/SaveModal',
    component: SaveModal,
    args: {
        title: 'Title',
        cancelLabel: 'Cancel',
        okLabel: 'Save',
    },
    argTypes: {
        title: { control: 'text' },
        cancelLabel: { control: 'text' },
        okLabel: { control: 'text' },
    },
} as Meta;

export const Default = setupStory({
    components: { Button, SaveModal },
    setup() {
        return useOpenState();
    },
    template: `
    <div>
        <Button @click="open">Open modal</Button>
        <SaveModal
            v-bind="args"
            :isOpen="isOpen"
            @close="close"
        >
            <p>Test</p>
        </SaveModal>
    </div>
`,
});
