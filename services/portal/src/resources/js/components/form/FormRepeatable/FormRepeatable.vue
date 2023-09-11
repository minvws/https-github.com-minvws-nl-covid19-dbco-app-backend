<template>
    <FormulateInput
        v-model="values"
        :name="context.name"
        :repeatable="true"
        type="group"
        :label="label"
        :disabled="disabled"
    >
        <template #default="{ index }">
            <FormulateInput
                name="value"
                :placeholder="placeholder"
                :disabled="disabled"
                value=""
                data-testid="text-field"
            />
        </template>
        <template #addmore="{ addMore }">
            <div class="d-inline-flex justify-content-start">
                <BButton block variant="primary" @click="addMore" :disabled="disabled" data-testid="add-button">
                    + Voeg toe
                </BButton>
            </div>
        </template>
        <template #remove="{ index, removeItem }">
            <BButton
                variant="secondary"
                :class="context.classes.groupRepeatableRemove"
                data-testid="remove-button"
                :disabled="disabled"
                @click.prevent="removeItem"
                v-text="context.removeLabel"
            />
        </template>
    </FormulateInput>
</template>

<script lang="ts">
import _ from 'lodash';
import { defineComponent } from 'vue';

type Values = {
    value: string;
}[];

export default defineComponent({
    name: 'FormRepeatable',
    data() {
        return {
            values: [] as Values,
        };
    },
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
        context: {
            type: Object,
            required: true,
        },
        label: {
            type: String,
            required: false,
        },
        placeholder: {
            type: String,
            required: true,
        },
    },
    created() {
        if (!this.context.model) return;
        this.context.model.forEach((value: string) => {
            this.values.push({ value });
        });
        this.submit = _.debounce(this.submit, 300);
    },
    watch: {
        values: {
            handler(values) {
                this.submit(values);
            },
        },
    },
    methods: {
        submit(values: Values) {
            if (!values) {
                this.context.model = [];
                return;
            }

            // Map array of objects with value property to flat array
            // Remove empty values from array
            this.context.model = values.map(({ value }) => value).filter((a) => !!a);
            this.$emit('change');
        },
    },
});
</script>
