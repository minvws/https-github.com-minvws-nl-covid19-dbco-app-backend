<template>
    <FormElementOutline v-slot="slotProps" :invalid="invalid">
        <!-- eslint-disable vuejs-accessibility/no-autofocus -->
        <input
            :type="type"
            :class="['tw-form-input', ...slotProps.styles]"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            :autoFocus="autoFocus"
            :placeholder="placeholder"
            :maxLength="maxLength"
            :aria-label="ariaLabel"
            :aria-errormessage="ariaErrormessage"
            :id="id"
            :name="name"
            :value="value"
            :step="step"
            v-on="$listeners"
        />
    </FormElementOutline>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import FormElementOutline from '../FormElementOutline/FormElementOutline.vue';

type InputType = 'text' | 'tel' | 'email' | 'password' | 'number' | 'date' | 'datetime-local' | 'time';
export default defineComponent({
    components: {
        FormElementOutline,
    },
    emits: {
        /* c8 ignore start */
        input: (event: InputEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: {
        type: { type: String as PropType<InputType>, default: 'text' },
        ariaLabel: { type: String },
        ariaErrormessage: { type: String },
        placeholder: { type: String },
        required: { type: Boolean },
        readonly: { type: Boolean },
        disabled: { type: Boolean },
        autoFocus: { type: Boolean },
        maxLength: { type: Number },
        id: { type: String },
        name: { type: String },
        value: { type: String },
        step: { type: Number },
        invalid: FormElementOutline.props.invalid,
    },
    setup() {
        return {};
    },
});
</script>
