<template>
    <div class="d-flex align-content-center">
        <FormulateInput type="checkbox" v-model="isActive" class="mb-0 mr-2" ignored :disabled="disabled" />
        <FormulateInput
            v-model="context.model"
            :name="context.name"
            :placeholder="defaultText"
            :type="inputType"
            class="ml-1 w100"
            rows="1"
            :element-class="['formulate-input-element--textarea--one-row']"
            @blur="checkInput"
            @click="isActive = true"
            :readonly="!isActive"
            :disabled="disabled"
            data-testid="text-input"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'FormInputCheckbox',
    props: {
        context: {
            type: Object,
            required: true,
        },
        defaultText: {
            type: String,
            required: true,
        },
        inputType: {
            type: String,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            isActive: !!this.context.model && this.context.model.length > 0,
        };
    },
    created() {
        this.$nextTick(function () {
            this.resizeToText();
        });
    },
    methods: {
        checkInput() {
            if (!this.context.model) {
                this.context.model = null;
                this.isActive = false;
            }
        },
        resizeToText() {
            const textArea = document.querySelector<HTMLElement>(
                'div.formulate-input-element--textarea--one-row textarea'
            );
            if (!textArea) return;

            if (textArea.scrollHeight > textArea.clientHeight) {
                textArea.style.height = textArea.scrollHeight + 2 + 'px';
            } else {
                textArea.style.height = '46px';
            }
        },
    },
    watch: {
        isActive(active) {
            if (active && !this.context.model) {
                this.context.model = this.defaultText;
                this.$emit('change');
            }
            if (!active) {
                this.context.model = null;
            }
        },
        'context.model'(value) {
            // Makes sure to update the checkbox on external changes
            this.isActive = value && value.length > 0;
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

::v-deep input::placeholder {
    color: $black !important;
}

::v-deep textarea {
    &::placeholder {
        color: $black !important;
    }
}
</style>
