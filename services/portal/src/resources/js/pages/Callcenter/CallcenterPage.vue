<template>
    <LayoutSidebar useFlexSpace sidebarPosition="left">
        <template #sidebar>
            <CallcenterSearch class="tw-h-full" />
        </template>

        <main class="tw-flex tw-flex-col tw-flex-auto tw-p-8">
            <CallcenterSearchResults />
        </main>
    </LayoutSidebar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import CallcenterSearch from '@/components/callcenter/CallcenterSearch/CallcenterSearch.vue';
import CallcenterSearchResults from '@/components/callcenter/CallcenterSearchResults/CallcenterSearchResults.vue';
import { LayoutSidebar } from '@dbco/ui-library';

export default defineComponent({
    name: 'CallcenterPage',
    components: {
        CallcenterSearch,
        CallcenterSearchResults,
        LayoutSidebar,
    },
});
</script>
