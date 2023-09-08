<template></template>
<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'InfiniteLoading',
    mounted() {
        this.$emit('infinite', {
            loaded: () => {
                // no-opt
            },
            complete: () => {
                // no-opt
            },
        });
    },
});
</script>
