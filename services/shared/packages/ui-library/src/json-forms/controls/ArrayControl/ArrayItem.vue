<template>
    <HStack class="tw-items-center">
        <div class="tw-flex-grow">
            <slot />
        </div>
        <HStack spacing="1">
            <IconButton
                v-if="showSortButtons"
                :disabled="!moveUpEnabled"
                variant="outline"
                icon="arrow-up"
                ariaLabel="move up"
                @click="$emit('moveUp', index)"
            />
            <IconButton
                v-if="showSortButtons"
                :disabled="!moveDownEnabled"
                variant="outline"
                icon="arrow-down"
                ariaLabel="move down"
                @click="$emit('moveDown', index)"
            />
            <IconButton
                :disabled="!deleteEnabled"
                variant="outline"
                icon="trash-can"
                ariaLabel="delete"
                @click="$emit('delete', index)"
            />
        </HStack>
    </HStack>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { HStack, IconButton } from '../../../components';

export default defineComponent({
    components: {
        IconButton,
        HStack,
    },
    emits: {
        /* c8 ignore next 3 */
        delete: (index: number) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        moveUp: (index: number) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        moveDown: (index: number) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        index: {
            type: Number,
            required: true,
        },
        showSortButtons: {
            type: Boolean,
        },
        moveUpEnabled: {
            type: Boolean,
            required: true,
        },
        moveDownEnabled: {
            type: Boolean,
            required: true,
        },
        deleteEnabled: {
            type: Boolean,
            required: true,
        },
    },
    setup() {},
});
</script>
