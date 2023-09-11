<template>
    <aside id="chore-sidebar">
        <header>
            <h3>{{ title }}</h3>
            <slot name="header"></slot>
        </header>
        <slot></slot>
        <p v-if="hint">{{ hint }}</p>
    </aside>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        hint: { type: String as PropType<string>, required: false },
        title: { type: String as PropType<string>, required: true },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#chore-sidebar {
    background: $white;
    border-left: $border-default;
    display: flex;
    flex-direction: column;

    header {
        border-bottom: $border-default;
        display: flex;
        align-items: center;
        padding: 1.5rem $padding-default;
        margin: 0;

        h3 {
            margin: 0;
            flex: 1 0 auto;
        }

        button {
            margin: 0;
            padding: 0;
            border: none;
            background: none;
        }
    }

    p {
        color: $bco-blue-gray;
        padding: $padding-default;
        text-align: center;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}
</style>
