<template>
    <LayoutSidebar useFlexSpace>
        <main class="tw-p-8">
            <Heading as="h1" size="lg" class="tw-mb-8">Taken</Heading>
            <CallToActionTable />
            <CallToActionErrorModal />
        </main>

        <template #sidebar>
            <CallToActionSidebar class="tw-h-full" />
        </template>
    </LayoutSidebar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import CallToActionTable from '@/components/callToAction/CallToActionTable/CallToActionTable.vue';
import CallToActionSidebar from '@/components/callToAction/CallToActionSidebar/CallToActionSidebar.vue';

import CallToActionErrorModal from '@/components/modals/CallToActionErrorModal/CallToActionErrorModal.vue';
import { Heading, LayoutSidebar } from '@dbco/ui-library';

export default defineComponent({
    components: { CallToActionErrorModal, CallToActionSidebar, CallToActionTable, Heading, LayoutSidebar },
    setup() {
        const isCallToActionEnabled = true;
        return {
            isCallToActionEnabled,
        };
    },
});
</script>
