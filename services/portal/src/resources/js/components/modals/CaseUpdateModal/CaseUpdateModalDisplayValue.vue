<template>
    <div class="case-update-modal-display-value">
        <div v-if="value === null">-</div>
        <div v-else-if="Array.isArray(value)">
            <ul>
                <li v-for="item in value">
                    <CaseUpdateModalDisplayValue :value="item" />
                </li>
            </ul>
        </div>
        <div v-else-if="typeof value === 'object'">
            <span v-for="field in value.fields"> {{ field.label }}: {{ field.displayValue }}<br /> </span>
        </div>
        <div v-else>
            {{ value }}
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';

type Field = { label: string; displayValue: string };
type Value = null | any[] | { fields: Field[] } | string;

export default {
    name: 'CaseUpdateModalDisplayValue',
    props: {
        value: {
            type: [Object, String, Array] as PropType<Value>,
            required: true,
        },
    },
    data() {
        return {};
    },
};
</script>
