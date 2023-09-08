<template>
    <Modal :isOpen="isOpen" @close="$emit('close')">
        <template v-slot:header>{{ title }}</template>
        <slot />

        <template v-slot:footer>
            <HStack class="tw-w-full">
                <Button
                    class="tw-flex-1"
                    :disabled="loading"
                    :variant="cancelIsPrimary ? 'solid' : 'outline'"
                    @click="$emit('close')"
                    >{{ cancelLabel }}</Button
                >
                <Button
                    class="tw-flex-1"
                    :disabled="okDisabled"
                    :loading="loading"
                    :variant="cancelIsPrimary ? 'outline' : 'solid'"
                    @click="$emit('ok')"
                    >{{ okLabel }}</Button
                >
            </HStack>
        </template>
    </Modal>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Modal from '../Modal/Modal.vue';
import Button from '../Button/Button.vue';
import HStack from '../Stack/HStack.vue';

export default defineComponent({
    components: {
        Modal,
        Button,
        HStack,
    },
    emits: {
        /* c8 ignore start */
        ok: () => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        close: () => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: {
        cancelLabel: { type: String, default: 'Annuleren' },
        cancelIsPrimary: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        okDisabled: { type: Boolean, default: false },
        okLabel: { type: String, default: 'Opslaan' },
        isOpen: Modal.props.isOpen,
        title: { type: String },
    },

    setup() {
        return {};
    },
});
</script>
