<template>
    <FormElementOutline v-slot="slotProps" :style="cssVars" :invalid="invalid">
        <!-- eslint-disable vuejs-accessibility/no-autofocus, vuejs-accessibility/no-onchange -->
        <select
            :class="[
                'tw-form-select',
                'tw-min-w-[172px]',
                ...slotProps.styles,
                'tw-bg-origin-content',
                'tw-bg-[size:20px]',
                'tw-bg-[right_-1.75rem_center]',
                'tw-bg-[image:var(--bg-image-url)]',
                'read-only:tw-bg-[image:none]',
                'read-only:tw-pr-3',
                placeholderSelected ? 'tw-text-gray-600' : '',
                ...sizeStyles[size],
                ...variantStyles[variant],
            ]"
            :disabled="disabled"
            :required="required"
            :autoFocus="autoFocus"
            :aria-label="ariaLabel"
            :id="id"
            :name="name"
            v-aria-readonly="readonly"
            v-on="$listeners"
            ref="selectRef"
            @change="handleChange"
        >
            <option v-if="hasPlaceholder" :value="PLACEHOLDER_VALUE">
                {{ placeholder }}
            </option>
            <slot />
        </select>
    </FormElementOutline>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, onMounted, ref } from 'vue';
import type { Extends, Sizes } from '../../types';
import FormElementOutline from '../FormElementOutline/FormElementOutline.vue';
import chevronSmDown from './chevron-sm-down.svg';

type Variant = 'outline' | 'plain';
export type Size = Extends<Sizes, 'sm' | 'md' | 'lg'>;

const sizeStyles: Record<Size, string[]> = {
    sm: ['tw-text-sm', 'tw-pt-[5px]', 'tw-pb-[5px]'],
    md: ['tw-text-md', 'tw-pt-[11px]', 'tw-pb-[11px]'],
    lg: ['tw-text-md', 'tw-pt-[13px]', 'tw-pb-[13px]'],
};

const variantStyles: Record<Variant, string[]> = {
    outline: [],
    plain: ['tw-text-violet-500', 'tw-bg-transparent', 'tw-border-none'],
};

const PLACEHOLDER_VALUE = '';

export default defineComponent({
    components: {
        FormElementOutline,
    },
    emits: {
        /* c8 ignore start */
        change: (event: ChangeEvent<HTMLSelectElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        blur: (event: FocusEvent<HTMLSelectElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: {
        ariaLabel: { type: String },
        placeholder: { type: String },
        required: { type: Boolean },
        readonly: { type: Boolean },
        disabled: { type: Boolean },
        autoFocus: { type: Boolean },
        id: { type: String },
        name: { type: String },
        value: { type: String },
        invalid: FormElementOutline.props.invalid,
        size: { type: String as PropType<Size>, default: 'md' },
        variant: { type: String as PropType<Variant>, default: 'outline' },
    },
    setup({ placeholder }) {
        const cssVars = { '--bg-image-url': `url(${chevronSmDown})` };
        const selectRef = ref<HTMLSelectElement | null>(null);

        const hasPlaceholder = placeholder !== undefined;
        const placeholderSelected = ref(hasPlaceholder);

        function handleChange(event: Event) {
            if (hasPlaceholder) {
                placeholderSelected.value = (event.target as HTMLSelectElement).value === PLACEHOLDER_VALUE;
            }
        }

        onMounted(() => {
            if (hasPlaceholder && selectRef.value) {
                placeholderSelected.value = selectRef.value.value === PLACEHOLDER_VALUE;
            }
        });

        return {
            cssVars,
            handleChange,
            placeholder,
            PLACEHOLDER_VALUE,
            hasPlaceholder,
            placeholderSelected,
            selectRef,
            sizeStyles,
            variantStyles,
        };
    },
});
</script>

<style lang="css" scoped>
select > option:first {
    color: red;
}

select > option[value='']:checked {
    background: red;
}
</style>
