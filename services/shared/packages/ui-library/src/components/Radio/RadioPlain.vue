<!-- eslint-disable vuejs-accessibility/no-autofocus -->
<template>
    <label
        :class="[
            'tw-inline-flex',
            'focus:tw-bg-red-500',
            'tw-items-center',
            'tw-gap-2',
            { 'tw-text-gray-300': disabled },
        ]"
        :for="id"
    >
        <FormElementOutline v-slot="slotProps" :invalid="invalid" useFocusOutline round>
            <!-- eslint-disable vuejs-accessibility/no-autofocus -->
            <input
                type="radio"
                :class="[
                    'tw-form-radio',
                    ...slotProps.styles,
                    '!tw-w-6',
                    'tw-h-6',
                    'tw-m-0',
                    'read-only:checked:tw-bg-gray-500',
                    ['checked:tw-text-violet-700', 'checked:tw-border-violet-700', 'checked:hover:tw-bg-violet-800'],
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

        <!-- @mousedown.prevent - prevents the focus from being stolen by the label text -->
        <span @mousedown.prevent>
            <slot />
        </span>
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
