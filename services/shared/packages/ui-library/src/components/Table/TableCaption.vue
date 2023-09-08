<template>
    <caption class="tw-mt-4 tw-text-center tw-px-6 tw-py-2 tw-body-sm tw-font-normal tw-text-gray-600">
        <slot />
    </caption>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({});
</script>

<style lang="css" scoped>
caption {
    caption-side: bottom;
}
</style>
