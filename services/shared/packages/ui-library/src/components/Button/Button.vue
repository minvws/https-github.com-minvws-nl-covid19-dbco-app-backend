<template>
    <ButtonOrLink
        v-tw-merge
        :id="buttonId"
        :href="href"
        :rel="rel"
        :target="target"
        :ariaLabel="ariaLabel"
        :to="to"
        :type="type"
        :disabled="disabled || loading"
        :class="[
            [
                'tw-no-underline',
                'tw-font-sans',
                'tw-font-medium',
                'tw-leading-normal',
                'tw-tracking-normal',
                'tw-rounded',
                'tw-border',
                'tw-border-solid',
                'tw-text-left',
                'tw-inline-flex',
                'tw-shrink-0',
                'tw-items-center',
                'tw-justify-center',
                'focus:tw-focus-outline',
                'hover:!tw-no-underline',
                ...sizeStyles[size],
                ...variantStyles[variant],
                ...colorStyles[`${color}-${variant}`],
            ],
            disabled || loading ? ['tw-opacity-50'] : 'tw-cursor-pointer',
        ]"
        v-on="$listeners"
    >
        <Spinner
            v-if="loading && loadingPosition === 'left'"
            role="progressbar"
            :aria-labelledby="buttonId"
            :class="iconStyle"
        />
        <Icon v-if="!loading && iconLeft" :name="iconLeft" aria-hidden="true" :class="iconStyle" />

        <span v-if="loading && loadingText">{{ loadingText }}</span>
        <slot v-else />

        <Spinner
            v-if="loading && loadingPosition === 'right'"
            role="progressbar"
            :aria-labelledby="buttonId"
            :class="iconStyle"
        />
        <Icon v-if="!loading && iconRight" :name="iconRight" aria-hidden="true" :class="iconStyle" />
    </ButtonOrLink>
</template>

<script lang="ts">
import type { Extends, Sizes, ThemeColor } from '../../types';
import type { IconName } from '../Icon/icons';
import type { PropType } from 'vue';
import { toRefs, computed, defineComponent } from 'vue';
import Icon from '../Icon/Icon.vue';
import Spinner from '../Spinner/Spinner.vue';
import ButtonOrLink from '../ButtonOrLink/ButtonOrLink.vue';
import { uniqueId } from 'lodash';

type Variant = 'solid' | 'outline' | 'plain';
type LoadingPosition = 'left' | 'right';
type Color = Extends<ThemeColor, 'violet' | 'red'>;
export type Size = Extends<Sizes, 'sm' | 'md' | 'lg'>;

const iconStyle = ['tw-inline-block tw-m-0 tw-w-[20px] tw-h-[20px] tw-shrink-0'];

const sizeStyles: Record<Size, string[]> = {
    sm: ['tw-text-sm', 'tw-py-[5px]', 'tw-px-3', 'tw-gap-1', 'tw-min-h-[32px]'],
    md: ['tw-text-md', 'tw-py-[11px]', 'tw-px-4', 'tw-gap-2', 'tw-min-h-[44px]'],
    lg: ['tw-text-md', 'tw-py-[13px]', 'tw-px-4', 'tw-gap-2.5', 'tw-min-h-[48px]'],
};

const variantStyles: Record<Variant, string[]> = {
    solid: ['tw-text-white', 'hover:!tw-text-white'],
    outline: ['tw-bg-white', 'tw-border-gray-200'],
    plain: ['tw-bg-transparent', 'tw-border-transparent', 'enabled:hover:tw-bg-darkAlpha-50'],
};

const colorStyles: Record<`${Color}-${Variant}`, string[]> = {
    ['violet-solid']: ['tw-bg-violet-700', 'tw-border-violet-700', 'hover:tw-bg-violet-800'],
    ['violet-outline']: [
        'tw-text-violet-700',
        'enabled:hover:!tw-text-violet-800',
        'enabled:hover:tw-border-violet-800',
    ],
    ['violet-plain']: ['tw-text-violet-700', 'enabled:hover:tw-bg-darkAlpha-50', 'enabled:hover:!tw-text-violet-800'],
    ['red-solid']: ['tw-bg-red-600', 'tw-border-red-600', 'enabled:hover:tw-bg-red-700'],
    ['red-outline']: ['tw-text-red-600', 'enabled:hover:!tw-text-red-700', 'enabled:hover:tw-border-red-700'],
    ['red-plain']: ['tw-text-red-600', 'enabled:hover:!tw-text-red-700'],
};

export default defineComponent({
    props: {
        ...ButtonOrLink.props,
        iconLeft: { type: String as PropType<IconName> },
        iconRight: { type: String as PropType<IconName> },
        size: { type: String as PropType<Size>, default: 'md' },
        variant: { type: String as PropType<Variant>, default: 'solid' },
        color: { type: String as PropType<Color>, default: 'violet' },
        id: { type: String },
        loading: { type: Boolean, default: false },
        loadingText: { type: String },
        loadingPosition: { type: String as PropType<LoadingPosition>, default: 'left' },
    },
    components: { ButtonOrLink, Spinner, Icon },
    setup(props) {
        let { id, iconLeft, iconRight } = toRefs(props);

        const defaultId = uniqueId('button-');
        const buttonId = computed(() => id.value || defaultId);

        return {
            buttonId,
            iconLeft,
            iconRight,
            iconStyle,
            sizeStyles,
            variantStyles,
            colorStyles,
        };
    },
});
</script>
