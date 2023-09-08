<template>
    <th
        class="tw-text-gray-600 tw-text-sm tw-uppercase tw-text-start tw-px-2 tw-py-3 first-of-type:tw-pl-6 last-of-type:tw-pr-6 tw-border-gray-200 tw-border-0 tw-border-b-[1px] tw-border-solid"
        :class="{ 'tw-text-end': isNumeric }"
    >
        <slot></slot>
    </th>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
    props: {
        isNumeric: Boolean,
    },
});
</script>
