<template>
    <OptionalWrapper :should-wrap="useFlexSpace" class="tw-relative tw-flex tw-flex-col tw-h-full tw-w-full tw-flex-1">
        <div
            class="tw-flex tw-flex-row tw-flex-auto tw-h-full"
            :class="{
                'tw-absolute tw-w-full': useFlexSpace,
            }"
        >
            <div class="tw-flex tw-flex-col tw-flex-auto tw-w-2/3 tw-overflow-y-auto tw-overflow-x-hidden">
                <slot></slot>
            </div>
            <div
                class="tw-flex tw-flex-col tw-flex-auto tw-w-1/3 tw-overflow-y-auto tw-overflow-x-hidden"
                :class="{
                    'tw-order-first': isSidebarOnLeft,
                }"
            >
                <slot name="sidebar"></slot>
            </div>
        </div>
    </OptionalWrapper>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import OptionalWrapper from '../OptionalWrapper/OptionalWrapper.vue';

type SidebarPosition = 'left' | 'right';

export default defineComponent({
    components: {
        OptionalWrapper,
    },
    props: {
        sidebarPosition: { type: String as PropType<SidebarPosition>, default: 'right' },
        useFlexSpace: { type: Boolean, default: false },
    },
    setup({ useFlexSpace, sidebarPosition }) {
        return { useFlexSpace, isSidebarOnLeft: sidebarPosition === 'left' };
    },
});
</script>
