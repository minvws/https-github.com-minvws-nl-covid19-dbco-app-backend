<template>
    <FormulateFormWrapper
        v-bind="context.attributes"
        v-model="values"
        @change="onChange"
        @repeatableRemoved="$emit('repeatableRemoved', $event)"
        :schema="[checkbox, chips]"
        :errors="errors"
    />
</template>

<script>
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';

export default {
    name: 'FormPresetOptions',
    data() {
        return {
            values: { [this.context.name]: this.context.model || [] },
            errors: null,
        };
    },
    props: {
        context: {
            type: Object,
            required: true,
        },
        checkboxLabel: {
            type: String,
        },
        presetLabel: {
            type: String,
        },
        preset: {
            type: Object,
        },
    },
    methods: {
        onChange() {
            // We need to update our context.model on change to make sure the changes of the underlying form are passed
            this.context.model = this.values[this.context.name];
            this.$emit('change');
        },
    },
    watch: {
        values: {
            handler(values) {
                if (!values) {
                    this.context.model = [];
                    return;
                }
                const { name } = this.context;
                const value = values[name];

                if (!value) {
                    this.context.model = [];
                    return;
                }

                this.context.model = value;
            },
            deep: true,
        },
    },
    computed: {
        checkbox() {
            return SchemaGenerator.orphanField(this.context.name)
                .checkbox(this.checkboxLabel, this.context.options)
                .toConfig();
        },
        chips() {
            return SchemaGenerator.orphanField(this.context.name)
                .chips(this.presetLabel, '', this.preset, 12)
                .toConfig();
        },
    },
};
</script>
