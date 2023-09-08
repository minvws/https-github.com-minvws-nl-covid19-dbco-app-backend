<template>
    <component v-tw-merge :is="as" :class="styles">
        <slot></slot>
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Extends, Sizes } from '../../types';

type Tag = 'div' | 'span' | 'section' | 'article';
type Size = Extends<Sizes, 'sm' | 'md' | 'lg' | 'xl'>;

const Styles: Record<Size, string[]> = {
    sm: ['tw-max-w-lg'],
    md: ['tw-max-w-3xl'],
    lg: ['tw-max-w-5xl'],
    xl: ['tw-max-w-[1440px]'],
};

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        size: { type: String as PropType<Size>, default: 'xl' },
        centerContent: Boolean,
    },
    setup({ as, size, centerContent }) {
        const styles = ['tw-block', 'tw-w-full', 'tw-px-4', 'tw-mx-auto', ...Styles[size]];

        if (centerContent) {
            styles.push('tw-flex', 'tw-flex-col', 'tw-items-center');
        }

        return {
            as,
            styles,
        };
    },
});
</script>
