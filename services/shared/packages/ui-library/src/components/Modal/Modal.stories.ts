import type { Meta } from '@storybook/vue';
import Button from '../Button/Button.vue';
import Modal from './Modal.vue';
import { setupStory } from '../../../docs/utils/story';
import { useOpenState } from '../../composables';

export default {
    title: 'Components/Modal',
    component: Modal,
} as Meta;

export const Default = setupStory({
    components: { Button, Modal },
    setup() {
        return useOpenState();
    },
    template: `
    <div>
        <Button @click="open">Open modal</Button>
        <Modal 
            :isOpen="isOpen"
            @close="close">
            <template v-slot:header>Title</template>
            Content
            <template v-slot:footer><Button variant="outline" @click="close">Close</Button></template>
        </Modal>
    </div>
    `,
});

export const Overflow = setupStory({
    components: { Button, Modal },
    setup() {
        return useOpenState();
    },
    template: `
    <div>
        <Button @click="open">Open modal</Button>
        <Modal 
            :isOpen="isOpen"
            @close="close">
            <template v-slot:header>Title</template>
            <div class="tw-min-h-[1000px]">Content</div>
            <template v-slot:footer><Button variant="outline" @click="close">Close</Button></template>
        </Modal>
    </div>
    `,
});
