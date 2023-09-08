<template>
    <div>
        <div ref="collapseRef" :id="collapseId">
            <slot />
        </div>
        <Link
            v-if="!contentFitsInsideCollapsedSize"
            @click="toggle"
            size="sm"
            class="tw-mt-4"
            :iconRight="isOpen ? 'chevron-sm-up' : 'chevron-sm-down'"
            :aria-expanded="isOpen"
            :aria-controls="collapseId"
            >{{ isOpen ? labelOpen : labelClosed }}</Link
        >
    </div>
</template>

<script lang="ts">
import { uniqueId } from 'lodash';
import { defineComponent, ref } from 'vue';
import { Link } from '..';
import { useCollapse } from '../../composables';

export default defineComponent({
    components: {
        Link,
    },
    props: {
        collapsedSize: {
            type: Number,
            default: 0,
        },
        initialIsOpen: {
            type: Boolean,
            default: false,
        },
        labelOpen: {
            type: String,
            required: true,
        },
        labelClosed: {
            type: String,
            required: true,
        },
    },
    setup({ collapsedSize, initialIsOpen }) {
        const collapseId = uniqueId('collapse-');
        const isOpen = ref(initialIsOpen);

        const toggle = () => (isOpen.value = !isOpen.value);

        const { collapseRef, contentFitsInsideCollapsedSize } = useCollapse({ collapsedSize, isOpen });

        return { collapseId, collapseRef, isOpen, toggle, contentFitsInsideCollapsedSize };
    },
});
</script>
