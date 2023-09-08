<template>
    <div class="dbco-version">BCO Portaal - versie {{ version }}</div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import env from '@/env';

export default defineComponent({
    name: 'DbcoVersion',
    data: () => ({
        version: env.version,
    }),
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.dbco-version {
    color: $light-grey;
    font-size: 0.75rem;
    padding-bottom: 1rem;
}
</style>
