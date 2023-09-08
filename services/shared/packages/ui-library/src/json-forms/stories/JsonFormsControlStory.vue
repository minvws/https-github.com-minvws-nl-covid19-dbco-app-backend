<template>
    <JsonFormsBaseStory v-bind="$props" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import JsonFormsBaseStory from '../core/JsonFormsBase/JsonFormsBaseStory.vue';

export default defineComponent({
    props: JsonFormsBaseStory.props,
    components: { JsonFormsBaseStory },
    setup() {},
});
</script>
