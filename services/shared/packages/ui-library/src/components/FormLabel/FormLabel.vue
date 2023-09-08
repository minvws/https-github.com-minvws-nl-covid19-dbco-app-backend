<template>
    <component v-tw-merge :is="as" :class="['tw-body-sm', 'tw-font-medium', 'tw-p-0', 'tw-m-0']">
        <slot />
        <span v-if="slots.extra" class="tw-font-normal tw-text-gray-600">
            <slot name="extra" />
        </span>
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { useSlots, defineComponent } from 'vue';

type Tag = 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' | 'p' | 'span' | 'div' | 'label' | 'legend';

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'label' },
    },
    setup() {
        return { slots: useSlots() };
    },
});
</script>
