<!-- eslint-disable vuejs-accessibility/no-autofocus -->
<template>
    <label
        :class="[
            'tw-relative',
            'tw-block',
            'tw-bg-white',
            'tw-cursor-pointer',
            'tw-py-3',
            'tw-px-4',
            'tw-font-bold',
            'tw-grow',
            'tw-basis-0',
            '!tw-text-violet-700',
            'tw-text-center',
        ]"
        :for="id"
    >
        <FormElementOutline
            v-slot="slotProps"
            :invalid="invalid"
            useFocusOutline
            square
            :class="['tw-absolute', 'tw-top-0', 'tw-left-0', 'tw-bottom-0', 'tw-right-0']"
        >
            <!-- eslint-disable vuejs-accessibility/no-autofocus -->
            <input
                type="radio"
                style="--bg-image-url"
                :class="[
                    ...slotProps.styles,
                    'tw-appearance-none',
                    'tw-absolute',
                    'tw-w-full',
                    'tw-h-full',
                    '!tw-bg-transparent',
                    '!tw-bg-[image:none]',
                    '!tw-m-0',
                    '!tw-p-0',
                    'tw-cursor-pointer',
                    'tw-top-0',
                    'tw-left-0',
                    value === 'true' ? '!tw-rounded-s-sm' : '!tw-rounded-e-sm',
                    invalid ? '!tw-border-transparent' : '!tw-border-gray-200',
                    [
                        'checked:!tw-border-violet-700',
                        'checked:focus:!tw-border-violet-700',
                        'checked:tw-shadow-[0_0_0_1px_theme(colors.violet.700)_inset]',
                    ],
                ]"
                :disabled="disabled"
                :required="required"
                :autoFocus="autoFocus"
                :aria-label="ariaLabel"
                :id="id"
                :name="name"
                :checked="checked"
                :value="value"
                v-aria-readonly="readonly"
                v-on="$listeners"
            />
        </FormElementOutline>

        <slot />
    </label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FormElementOutline from '../FormElementOutline/FormElementOutline.vue';

import { props } from './radio-props';

export default defineComponent({
    components: { FormElementOutline },
    emits: {
        /* c8 ignore next */
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        ...props,
        id: { type: String, required: true },
    },
});
</script>
