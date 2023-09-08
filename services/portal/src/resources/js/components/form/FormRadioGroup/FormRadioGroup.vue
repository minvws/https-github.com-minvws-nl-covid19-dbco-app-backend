<template>
    <fieldset class="radio-toggle-group" :aria-labelledby="groupLabelIdentifier">
        <div class="radio-toggle-group__buttons" :class="{ 'pb-4': isOpen }" @change="onChange">
            <legend class="radio-toggle-group__buttons__label" :id="groupLabelIdentifier">{{ label }}</legend>
            <!-- legend should be decendent of fieldset, fieldset cannot be flexbox styled; thats why this extra div / labelled-by attribute is here. see issue: https://stackoverflow.com/questions/28078681/why-cant-fieldset-be-flex-containers -->
            <FormulateFormWrapper
                v-model="values"
                :schema="[radio]"
                @change="onChange"
                class="col-6"
                :disabled="radio.disabled"
                :class="{
                    open: isOpen,
                }"
            />
        </div>
        <div v-show="isOpen" class="radio-toggle-group__children pt-4">
            <div @change="onChange" :class="[childrenWrapperClass ? childrenWrapperClass : 'row']">
                <slot />
            </div>
        </div>
    </fieldset>
</template>

<script>
import { formLabelValue } from '@/utils/form';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { uniqueId } from 'lodash';

export default {
    name: 'FormRadioGroup',
    props: {
        context: {
            type: Object,
            required: true,
        },
        title: {
            type: [String, Function],
            required: true,
        },
        expand: {
            type: Array,
            required: true,
        },
        childrenWrapperClass: {
            type: String,
            required: false,
        },
    },
    data() {
        return {
            groupLabelIdentifier: uniqueId('FormRadioGroup'),
            values: {
                input: this.context.model,
            },
        };
    },
    methods: {
        onChange() {
            this.$emit('change');
        },
    },
    watch: {
        'context.model'(val) {
            // React to store changes
            this.values.input = val;
        },
        values: {
            handler(values) {
                this.context.model = values['input'];
            },
            immediate: true,
        },
    },
    computed: {
        isOpen() {
            return this.expand.includes(this.context.model);
        },
        radio() {
            return SchemaGenerator.orphanField('input').radioButton('', this.context.options).toConfig();
        },
        label() {
            return formLabelValue(this.title);
        },
    },
};
</script>
