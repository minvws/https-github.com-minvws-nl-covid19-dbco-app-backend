<template>
    <HStack class="tw-text-red-600 tw-body-sm" spacing="1">
        <Icon name="exclamation-mark-triangle-outline" />
        <ul class="tw-list-none tw-p-0 tw-m-0">
            <li v-for="message in messages">{{ message }}</li>
        </ul>
    </HStack>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import HStack from '../Stack/HStack.vue';
import Icon from '../Icon/Icon.vue';

export default defineComponent({
    components: { Icon, HStack },
    props: {
        messages: { type: Array as PropType<string[]>, required: true },
    },
    setup() {},
});
</script>
