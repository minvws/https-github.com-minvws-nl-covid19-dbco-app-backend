<template>
    <Stack :as="as" :spacing="spacing" direction="row">
        <slot />
    </Stack>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Stack from './Stack.vue';

export default defineComponent({
    components: { Stack },
    props: {
        as: Stack.props.as,
        spacing: Stack.props.spacing,
    },
});
</script>
