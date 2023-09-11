<template>
    <Button
        :href="href"
        :rel="rel"
        :target="target"
        :iconLeft="icon"
        :type="type"
        :size="size"
        :variant="variant"
        :color="color"
        :id="id"
        :disabled="disabled"
        :loading="loading"
        :ariaLabel="ariaLabel"
        :class="['tw-p-0', 'tw-overflow-hidden', ...sizeStyles[size]]"
        v-on="$listeners"
    />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { Size } from '../Button/Button.vue';
import { default as Button } from '../Button/Button.vue';

const { href, rel, target, iconLeft, type, size, variant, color, id, disabled, loading, ariaLabel } = Button.props;

const sizeStyles: Record<Size, string[]> = {
    sm: ['tw-w-8', 'tw-h-8'],
    md: ['tw-w-11', 'tw-h-11'],
    lg: ['tw-w-12', 'tw-h-12'],
};

export default defineComponent({
    props: {
        href,
        rel,
        target,
        type,
        size,
        variant,
        color,
        id,
        disabled,
        loading,
        icon: {
            ...iconLeft,
            required: true,
        },
        ariaLabel: {
            ...ariaLabel,
            required: true,
        },
    },
    components: { Button },
    setup() {
        return { sizeStyles };
    },
});
</script>
