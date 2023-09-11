<template>
    <div v-tw-merge :is="as" :class="['tw-flex', ...spacingStyles[spacing], ...directionStyles[direction]]">
        <slot />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Extends, ThemeSpacing } from '../../types';

type Tag = 'div' | 'section' | 'article' | 'p';
type Spacing = Extends<`${ThemeSpacing}`, '0' | '0.5' | '1' | '2' | '3' | '4' | '6' | '8' | '10'>;

type Direction = 'column' | 'row';

const spacingStyles: Record<Spacing, string[]> = {
    0: [],
    0.5: ['tw-gap-0.5'],
    1: ['tw-gap-1'],
    2: ['tw-gap-2'],
    3: ['tw-gap-3'],
    4: ['tw-gap-4'],
    6: ['tw-gap-6'],
    8: ['tw-gap-8'],
    10: ['tw-gap-10'],
};

const directionStyles: Record<Direction, string[]> = {
    row: ['tw-flex-row'],
    column: ['tw-flex-col'],
};

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        spacing: { type: String as PropType<Spacing>, default: '4' },
        direction: { type: String as PropType<Direction>, default: 'column' },
    },
    setup() {
        return {
            spacingStyles,
            directionStyles,
        };
    },
});
</script>
