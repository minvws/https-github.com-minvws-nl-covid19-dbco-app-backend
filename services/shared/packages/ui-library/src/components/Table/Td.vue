<template>
    <td
        v-tw-merge
        class="tw-body-sm tw-font-normal tw-text-gray-950 tw-px-2 tw-py-4 first-of-type:tw-pl-6 last-of-type:tw-pr-6 tw-border-gray-200 tw-border-0 tw-border-b-[1px] tw-border-solid"
        :class="{ 'tw-text-end': isNumeric }"
    >
        <slot></slot>
    </td>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
    props: {
        isNumeric: Boolean,
    },
});
</script>
