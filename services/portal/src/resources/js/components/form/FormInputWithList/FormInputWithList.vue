<template>
    <div>
        <datalist :id="listId">
            <option v-for="(item, index) in list" :key="index" :value="item"></option>
        </datalist>

        <FormulateInput
            v-model="context.model"
            v-bind="context.attributes"
            :placeholder="placeholder"
            :disabled="disabled"
            :list="listId"
        />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Context } from '@braid/vue-formulate';
import { uniqueId } from 'lodash';

export default defineComponent({
    name: 'FormInputWithList',
    props: {
        context: {
            type: Object as PropType<Context>,
            required: true,
        },
        list: {
            type: Array as PropType<string[]>,
            required: true,
        },
        placeholder: {
            type: String,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            listId: uniqueId('form-input-with-list'),
        };
    },
});
</script>
