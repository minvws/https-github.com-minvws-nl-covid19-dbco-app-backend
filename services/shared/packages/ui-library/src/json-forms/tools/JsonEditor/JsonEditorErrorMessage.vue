<template>
    <div class="json-editor-message">
        <div>
            <div class="jse-text-centered">
                <svg version="1.1" class="fa-icon" width="16" height="16" role="presentation" viewBox="0 0 512 512">
                    <path
                        d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"
                    ></path>
                </svg>
                <slot />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({});
</script>

<style lang="scss" scoped>
.json-editor-message {
    background: var(--jse-message-error-background);
    color: var(--jse-message-error-color);
    font-family: var(--jse-font-family-mono);
    font-size: var(--jse-font-size-mono);
    padding: var(--jse-padding);
    display: flex;
    gap: var(--jse-padding);
    flex-wrap: wrap;
    align-items: stretch;
}

svg {
    display: inline-block;
    fill: currentColor;
}
</style>
