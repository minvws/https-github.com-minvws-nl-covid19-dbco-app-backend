<!-- eslint-disable vuejs-accessibility/no-autofocus -->
<template>
    <label
        :style="cssVars"
        :class="[
            'tw-inline-flex',
            'focus:tw-bg-red-500',
            'tw-items-center',
            'tw-gap-2',
            { 'tw-text-gray-300': disabled },
        ]"
        :for="boxId"
    >
        <FormElementOutline v-slot="slotProps" :invalid="invalid" useFocusOutline>
            <!-- eslint-disable vuejs-accessibility/no-autofocus -->
            <input
                type="checkbox"
                :class="[
                    'tw-form-checkbox',
                    ...slotProps.styles,
                    '!tw-w-6',
                    'tw-h-6',
                    'tw-m-0',
                    'focus:tw-focus-outline',
                    'read-only:checked:tw-bg-gray-500',
                    [
                        'checked:tw-text-violet-700',
                        'checked:tw-border-violet-700',
                        'checked:hover:tw-bg-violet-800',
                        'checked:tw-p-px',
                        'checked:tw-bg-origin-content',
                        'checked:tw-bg-[size:20px]',
                        'checked:tw-bg-[image:var(--bg-image-url)]',
                    ],
                ]"
                :disabled="disabled"
                :required="required"
                :autoFocus="autoFocus"
                :aria-label="ariaLabel"
                :aria-errormessage="ariaErrormessage"
                :id="boxId"
                :name="name"
                :checked="checked"
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
import { uniqueId } from 'lodash';
import { defineComponent } from 'vue';
import FormElementOutline from '../FormElementOutline/FormElementOutline.vue';
import checkMark from './check-mark.svg';

export default defineComponent({
    components: { FormElementOutline },
    emits: {
        /* c8 ignore next */
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        ariaLabel: { type: String },
        ariaErrormessage: { type: String },
        checked: { type: Boolean },
        disabled: { type: Boolean },
        id: { type: String },
        name: { type: String },
        required: { type: Boolean },
        readonly: { type: Boolean },
        autoFocus: { type: Boolean },
        invalid: FormElementOutline.props.invalid,
    },
    setup(props) {
        const { id } = props;
        const cssVars = { '--bg-image-url': `url(${checkMark})` };

        return {
            boxId: id || uniqueId('checkbox'),
            cssVars,
        };
    },
});
</script>
