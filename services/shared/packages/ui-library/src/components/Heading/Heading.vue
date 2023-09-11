<template>
    <component v-tw-merge :is="as" :class="styles">
        <slot></slot>
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Extends, Sizes } from '../../types';

type Tag = 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' | 'p' | 'span' | 'div';
type Size = Extends<Sizes, 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'>;

const Styles: Record<Size, string[]> = {
    xs: ['tw-text-md'],
    sm: ['tw-text-lg'],
    md: ['tw-text-xl'],
    lg: ['tw-text-2xl'],
    xl: ['tw-text-3xl'],
    ['2xl']: ['tw-text-4xl'],
};

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'h2' },
        size: { type: String as PropType<Size>, default: 'md' },
        strong: { type: Boolean, default: true },
    },
    setup({ as, size, strong }) {
        const preflightStyles = ['tw-m-0'];
        const styles = [...preflightStyles, 'tw-font-sans', 'tw-leading-tight', 'tw-max-w-[75ch]', ...Styles[size]];

        if (strong) styles.push('tw-font-bold');

        return {
            as,
            styles,
            preflightStyles,
        };
    },
});
</script>
