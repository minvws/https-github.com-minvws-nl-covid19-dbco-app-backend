<template>
    <div
        class="tw-block tw-w-full"
        :style="{
            'font-size': '42px',
        }"
    >
        <div :class="className">The quick brown fox jumps over the lazy dog</div>
        <InfoLabel>
            {{ name }}
        </InfoLabel>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import InfoLabel from './InfoLabel.vue';

export default defineComponent({
    components: {
        InfoLabel,
    },
    props: {
        name: { type: String },
        className: { type: String },
    },
    setup({ className, name }) {
        return {
            name,
            className,
        };
    },
});
</script>
