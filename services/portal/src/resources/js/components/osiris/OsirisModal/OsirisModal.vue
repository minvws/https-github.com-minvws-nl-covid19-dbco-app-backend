<template>
    <div>
        <BModal v-if="step === 'loading'" visible hide-header hide-footer>
            <Spinner size="lg" />
        </BModal>

        <OsirisCaseValidationResult
            v-if="step === 'case-validation' && !!caseValidationMessages"
            @ok.prevent="handleValidationOk"
            @hidden="resetSteps"
            :validationMessages="caseValidationMessages"
        />

        <OsirisCaseStatus v-else-if="step === 'case-status'" :covid-case="covidCase" @cancel="resetSteps" />
    </div>
</template>

<script lang="ts">
import { useEventBus } from '@/composables/useEventBus';
import type { CaseValidationMessages } from '@dbco/portal-api/case.dto';
import { getOsirisValidationStatusMessages } from '@dbco/portal-api/client/case.api';
import { Spinner } from '@dbco/ui-library';
import { computed, defineComponent, onBeforeUnmount, onMounted, ref } from 'vue';
import OsirisCaseStatus from '../OsirisCaseStatus/OsirisCaseStatus.vue';
import OsirisCaseValidationResult from '../OsirisCaseValidationResult/OsirisCaseValidationResult.vue';

type Steps = 'loading' | 'case-status' | 'case-validation';

export default defineComponent({
    name: 'OsirisModal',
    components: {
        OsirisCaseStatus,
        OsirisCaseValidationResult,
        Spinner,
    },
    props: {
        covidCase: {
            type: Object,
            required: true,
        },
    },
    setup({ covidCase }) {
        const step = ref<Steps | null>(null);
        const eventBus = useEventBus();

        const caseValidationMessages = ref<CaseValidationMessages | null>(null);
        const hasCaseValidationMessages = computed(() => {
            if (!caseValidationMessages.value) return false;
            const { fatal, notice, warning } = caseValidationMessages.value;
            return caseValidationMessages.value && !![fatal, notice, warning].flat().length;
        });

        const handleOpenOsirisModal = async () => {
            step.value = 'loading';

            try {
                const validationMessages = await getOsirisValidationStatusMessages(covidCase.uuid);
                caseValidationMessages.value = validationMessages;

                step.value = hasCaseValidationMessages.value ? 'case-validation' : 'case-status';
            } catch (error) {
                console.error(error);
                throw error;
            }
        };

        onMounted(() => {
            eventBus.$on('open-osiris-modal', handleOpenOsirisModal);
        });

        onBeforeUnmount(() => {
            eventBus.$off('open-osiris-modal', handleOpenOsirisModal);
        });

        const resetSteps = () => {
            step.value = null;
        };

        const handleValidationOk = () => {
            step.value = 'case-status';
        };

        return {
            step,
            close,
            handleValidationOk,
            resetSteps,
            caseValidationMessages,
        };
    },
});
</script>
