repeatable-group
<template>
    <FormulateInput
        v-model="values"
        :addLabel="context.addLabel"
        :name="context.name"
        :minimum="context.minimum"
        :limit="context.limit"
        repeatable
        :disabled="disabled"
        :class="['repeatable-group', { 'repeatable-group--one': contextLimitIsOne }]"
        data-testid="input-repeatable-group"
        type="group"
        @repeatableRemoved="repeatableRemoved"
    >
        <template #addmore="{ addMore }">
            <div class="d-inline-flex justify-content-start">
                <BButton block variant="primary" @click="addMore" :disabled="disabled" data-testid="add-button">
                    {{ context.addLabel }}
                </BButton>
            </div>
        </template>
        <template #default="{ index }">
            <DataProvider :index="index">
                <slot />
            </DataProvider>
        </template>
        <template #remove="{ index, removeItem }">
            <BButton
                :class="context.classes.groupRepeatableRemove"
                :data-disabled="context.model.length <= context.minimum"
                role="button"
                :disabled="disabled"
                data-testid="remove-button"
                @click.prevent="removeItem"
                v-text="context.removeLabel"
            />
        </template>
    </FormulateInput>
</template>

<script lang="ts">
import _ from 'lodash';
import DataProvider from '@/components/utils/DataProvider/DataProvider.vue';
import { unflatten } from '@/utils/object';
import { getInputNames } from '@/utils/schema';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { FormField, VueFormulateContext } from '../ts/formTypes';

export default defineComponent({
    name: 'FormRepeatableGroup',
    components: {
        DataProvider,
    },
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
        context: {
            type: Object as PropType<VueFormulateContext>,
            required: true,
        },
        childrenSchema: {
            type: Array as PropType<FormField[]>,
            required: true,
        },
    },
    data() {
        return {
            values:
                [...this.context.model] ||
                ([] as Array<{
                    value: string;
                }>),
        };
    },
    created() {
        this.updateModel = _.debounce(this.updateModel, 300);
    },
    computed: {
        contextLimitIsOne() {
            return this.context.limit === 1;
        },
        emptySchemaFields() {
            return unflatten(getInputNames(this.childrenSchema).reduce((acc, cur) => ({ ...acc, [cur]: null }), {}));
        },
    },
    methods: {
        updateModel() {
            // Backend expects every key to be in every object
            // Make sure every child has a their key set in the object
            this.context.model = this.values.map((item) => ({
                ...this.emptySchemaFields,
                ...item,
            }));
            this.$emit('change');
        },
        repeatableRemoved() {
            this.$emit('repeatableRemoved', { values: this.values, name: this.context.name });
        },
    },
    watch: {
        values: {
            handler() {
                this.updateModel();
            },
            deep: true,
        },
    },
});
</script>
