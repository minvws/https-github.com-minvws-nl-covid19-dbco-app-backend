<template>
    <Backdrop :isOpen="isOpen" @close="$emit('close')">
        <div
            class="tw-relative tw-flex tw-flex-col tw-justify-between tw-w-[440px] tw-bg-white tw-border tw-border-solid tw-border-gray-200 tw-rounded-md tw-shadow-lg tw-p-6 tw-my-16"
        >
            <div class="tw-flex tw-w-full tw-justify-between">
                <Heading as="p" size="sm"><slot name="header" /></Heading>
                <IconButton
                    class="tw--mt-3 tw--mr-3"
                    ariaLabel="Sluiten"
                    icon="cross"
                    variant="plain"
                    @click="$emit('close')"
                />
            </div>
            <div class="tw-py-4"><slot /></div>
            <slot name="footer" />
        </div>
    </Backdrop>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Backdrop from '../Backdrop/Backdrop.vue';
import Heading from '../Heading/Heading.vue';
import IconButton from '../IconButton/IconButton.vue';

export default defineComponent({
    components: {
        Backdrop,
        Heading,
        IconButton,
    },
    emits: {
        /* c8 ignore start */
        close: () => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: Backdrop.props,

    setup() {
        return {};
    },
});
</script>
