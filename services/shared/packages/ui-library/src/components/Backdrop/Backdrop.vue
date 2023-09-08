<template>
    <Portal>
        <div
            class="tw-fixed tw-top-0 tw-left-0 tw-w-screen tw-h-screen tw-h-[100dvh] tw-overflow-y-auto tw-z-[1040]"
            v-if="isOpen"
        >
            <div class="tw-relative tw-flex tw-justify-center tw-min-h-screen tw-min-h-[100dvh]">
                <!--eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
                <div
                    class="tw-absolute tw-inset-0 tw-bg-darkAlpha-500 tw-z-[1050]"
                    role="dialog"
                    @click="$emit('close')"
                />
                <div class="tw-relative tw-z-[1060]"><slot /></div>
            </div>
        </div>
    </Portal>
</template>

<script lang="ts">
import { defineComponent, onBeforeUnmount, onMounted } from 'vue';
import { Portal } from '@linusborg/vue-simple-portal';
import Button from '../Button/Button.vue';

export default defineComponent({
    components: {
        Button,
        Portal,
    },
    emits: {
        /* c8 ignore start */
        close: () => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: {
        isOpen: { type: Boolean, default: false },
    },

    setup(props, { emit }) {
        const handleKeyUp = (event: KeyboardEvent) => {
            if (event.key === 'Escape') emit('close');
        };

        onMounted(() => {
            window.addEventListener('keyup', handleKeyUp);
        });

        onBeforeUnmount(() => {
            window.removeEventListener('keyup', handleKeyUp);
        });
    },
});
</script>
