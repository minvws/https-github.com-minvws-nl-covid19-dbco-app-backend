<template>
    <ButtonOrLink
        v-tw-merge
        :href="href"
        :rel="rel"
        :target="target"
        :ariaLabel="ariaLabel"
        :to="to"
        :type="type"
        :disabled="disabled"
        :class="[
            'tw-inherit-text-align',
            'tw-font-sans',
            'tw-border-none',
            'tw-bg-transparent',
            'tw-inline-flex',
            'tw-items-center',
            'tw-text-violet-700',
            'focus:tw-focus-outline',
            ...sizeStyles[size || 'default'],
            ...variantStyles[variant],
            disabled ? ['tw-opacity-50', 'tw-cursor-default'] : ['tw-cursor-pointer', ...variantHoverStyles[variant]],
        ]"
        v-on="$listeners"
    >
        <Icon v-if="iconLeft" :name="iconLeft" aria-hidden="true" :class="iconStyle" />

        <slot />

        <Icon v-if="iconRight" :name="iconRight" aria-hidden="true" :class="iconStyle" />
    </ButtonOrLink>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRefs } from 'vue';
import type { Extends, Sizes } from '../../types';
import Icon from '../Icon/Icon.vue';
import ButtonOrLink from '../ButtonOrLink/ButtonOrLink.vue';
import type { IconName } from '../Icon/icons';

type Variant = 'underlined' | 'plain';
type Size = Extends<Sizes, 'sm' | 'md' | 'lg'>;

const iconStyle = ['tw-inline-block tw-m-0 tw-w-[20px] tw-h-[20px]'];

const sizeStyles: Record<Size | 'default', string[]> = {
    default: ['tw-gap-2'],
    sm: ['tw-body-sm', 'tw-gap-1'],
    md: ['tw-body-md', 'tw-gap-2'],
    lg: ['tw-body-lg', 'tw-gap-2.5'],
};

const variantStyles: Record<Variant, string[]> = {
    underlined: ['tw-underline'],
    plain: ['tw-no-underline'],
};

const variantHoverStyles: Record<Variant, string[]> = {
    underlined: ['hover:tw-no-underline'],
    plain: ['hover:tw-underline'],
};

export default defineComponent({
    props: {
        ...ButtonOrLink.props,
        iconLeft: { type: String as PropType<IconName> },
        iconRight: { type: String as PropType<IconName> },
        size: { type: String as PropType<Size> },
        variant: { type: String as PropType<Variant>, default: 'plain' },
    },
    components: { ButtonOrLink, Icon },
    setup(props) {
        const { iconLeft, iconRight } = toRefs(props);

        return {
            iconLeft,
            iconRight,
            iconStyle,
            sizeStyles,
            variantStyles,
            variantHoverStyles,
        };
    },
});
</script>
