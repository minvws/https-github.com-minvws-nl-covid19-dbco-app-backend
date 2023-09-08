<template>
    <div
        role="tabpanel"
        :class="[isActive ? '' : 'tw-hidden']"
        :hidden="!isActive"
        :aria-labelledby="tabId"
        :id="tabPanelId"
    >
        <slot />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { injectTabsState } from './use-tabs-state';

export default defineComponent({
    setup() {
        const { isActive, tabId, tabPanelId } = injectTabsState();
        return { isActive, tabId, tabPanelId };
    },
});
</script>
