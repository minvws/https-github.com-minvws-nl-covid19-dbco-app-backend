<template>
    <div>
        <slot />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, onBeforeUnmount, toRef } from 'vue';
import type { TabClickEvent, TabsChangeEvent } from './types';
import type { TabVariant } from './use-tabs-state';
import { provideTabsState } from './use-tabs-state';

export default defineComponent({
    emits: {
        /* c8 ignore next */
        change: (event: TabsChangeEvent) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        index: {
            type: Number,
        },
        variant: {
            default: 'underline',
            type: String as PropType<TabVariant>,
        },
    },
    setup(props, { emit }) {
        const { currentIndex, eventBus, isControlled } = provideTabsState({
            index: toRef(props, 'index'),
            variant: toRef(props, 'variant'),
        });

        function handleTabClick(event: TabClickEvent) {
            if (event.tabIndex === currentIndex.value) return;

            if (!isControlled) {
                currentIndex.value = event.tabIndex;
            }

            emit('change', event);
        }

        eventBus.$on('tab-click', handleTabClick);

        onBeforeUnmount(() => {
            eventBus.$off('tab-click', handleTabClick);
        });
    },
});
</script>
