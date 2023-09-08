<template>
    <LayoutSidebar useFlexSpace>
        <main class="tw-p-8">
            <header class="tw-flex tw-justify-between tw-items-start tw-mb-8">
                <Heading as="h1" size="lg" class="tw-mb-8">{{ $t('pages.medicalSupervisor.title') }}</Heading>
                <NavigatorInput />
            </header>

            <QuestionTable />
            <SupervisionBackendErrorModal />
        </main>

        <template #sidebar>
            <QuestionSidebar class="tw-h-full" />
        </template>
    </LayoutSidebar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { mapMutations } from 'vuex';

import NavigatorInput from '@/components/supervision/NavigatorInput/NavigatorInput.vue';
import QuestionSidebar from '@/components/supervision/QuestionSidebar/QuestionSidebar.vue';
import QuestionTable from '@/components/supervision/QuestionTable/QuestionTable.vue';

import SupervisionBackendErrorModal from '@/components/modals/SupervisionBackendErrorModal/SupervisionBackendErrorModal.vue';
import { SupervisionRoles } from '@dbco/portal-api/user';
import { Heading, LayoutSidebar } from '@dbco/ui-library';

export default defineComponent({
    name: 'MedicalSupervisorPage',
    components: {
        NavigatorInput,
        SupervisionBackendErrorModal,
        QuestionSidebar,
        QuestionTable,
        Heading,
        LayoutSidebar,
    },
    created() {
        this.setActiveRole(SupervisionRoles.MEDICAL_SUPERVISION);
    },
    methods: {
        ...mapMutations({ setActiveRole: 'supervision/SET_ACTIVE_ROLE' }),
    },
});
</script>
