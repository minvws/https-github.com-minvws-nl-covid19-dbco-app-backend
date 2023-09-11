<template>
    <component :is="as" :class="styles" v-tw-merge><slot /></component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

const variantStyles = {
    plain: ['tw-border-0'],
    outline: ['tw-border', 'tw-border-solid'],
};

const colorsStyles = {
    none: [],
    gray: ['tw-border-gray-700/50', 'tw-bg-gray-200/30'],
    violet: ['tw-border-violet-700/50', 'tw-bg-violet-200/30'],
    green: ['tw-border-green-700/50', 'tw-bg-green-200/30'],
    blue: ['tw-border-blue-700/50', 'tw-bg-blue-200/30'],
    yellow: ['tw-border-yellow-700/50', 'tw-bg-yellow-200/30'],
};

type Color = keyof typeof colorsStyles;
type Variant = keyof typeof variantStyles;

export default defineComponent({
    props: {
        noPadding: Boolean,
        centerContent: Boolean,
        as: { type: String, default: 'div' },
        color: { type: String as PropType<Color>, default: 'none' },
        variant: { type: String as PropType<Variant>, default: 'plain' },
    },
    emits: ['click'],
    setup({ as, color, variant, centerContent, noPadding }) {
        const styles = [
            noPadding ? ' tw-p-0' : 'tw-p-2',
            'tw-font-sans',
            'tw-font-normal',
            'tw-body-md',
            ...variantStyles[variant],
            ...colorsStyles[color],
        ];

        if (centerContent) {
            styles.push('tw-flex', 'tw-flex-col', 'tw-items-center', 'tw-justify-center');
        }

        return { as, styles };
    },
});
</script>
