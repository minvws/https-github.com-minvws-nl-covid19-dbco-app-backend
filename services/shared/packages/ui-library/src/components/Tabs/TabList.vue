<template>
    <HStack role="tablist" spacing="0" :class="[...variantStyles[variant]]">
        <slot />
    </HStack>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import HStack from '../../components/Stack/HStack.vue';
import type { TabVariant } from './use-tabs-state';
import { injectTabsState } from './use-tabs-state';

const variantStyles: Record<TabVariant, string[]> = {
    pill: ['tw-w-full', 'tw-justify-center', 'tw-mb-3'],
    underline: [
        'tw-relative',
        'tw-z-0',
        `after:tw-content-['']`,
        'after:tw-absolute',
        'after:tw-block',
        'after:tw-bottom-0',
        'after:tw-left-0',
        'after:tw-w-full',
        'after:tw-h-[2px]',
        'after:tw-bg-gray-200',
    ],
};

export default defineComponent({
    setup() {
        const { variant } = injectTabsState();

        return { variant, variantStyles };
    },
    components: { HStack },
});
</script>
