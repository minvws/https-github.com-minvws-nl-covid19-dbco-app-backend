import type { Meta } from '@storybook/vue';
import Button from '../Button/Button.vue';
import Backdrop from './Backdrop.vue';
import { setupStory } from '../../../docs/utils/story';
import { useOpenState } from '../../composables';

export default {
    title: 'Components/Backdrop',
    component: Backdrop,
} as Meta;

export const Default = setupStory({
    components: { Button, Backdrop },
    setup() {
        return useOpenState();
    },
    template: `
    <div>
        <Button @click="open">Open backdrop</Button>
        <Backdrop 
            :isOpen="isOpen"
            @close="close">
            <div class="tw-bg-white tw-w-96 tw-h-44 tw-border tw-rounded tw-mt-16 tw-p-4">Content</div>
        </Backdrop>
    </div>
    `,
});
