<template>
    <FormElementOutline v-slot="slotProps" :invalid="invalid">
        <!-- eslint-disable vuejs-accessibility/no-autofocus -->
        <textarea
            :class="['tw-form-textarea', ...slotProps.styles]"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            :autoFocus="autoFocus"
            :placeholder="placeholder"
            :maxLength="maxLength"
            :aria-label="ariaLabel"
            :id="id"
            :name="name"
            v-on="$listeners"
            >{{ value }}</textarea
        >
    </FormElementOutline>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FormElementOutline from '../FormElementOutline/FormElementOutline.vue';

export default defineComponent({
    components: {
        FormElementOutline,
    },
    emits: {
        /* c8 ignore start */
        input: (event: InputEvent<HTMLTextAreaElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        change: (event: ChangeEvent<HTMLTextAreaElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
        /* c8 ignore stop */
    },
    props: {
        ariaLabel: { type: String },
        placeholder: { type: String },
        required: { type: Boolean },
        readonly: { type: Boolean },
        disabled: { type: Boolean },
        autoFocus: { type: Boolean },
        maxLength: { type: Number },
        id: { type: String },
        name: { type: String },
        value: { type: String },
        invalid: FormElementOutline.props.invalid,
    },
    setup() {
        return {};
    },
});
</script>
