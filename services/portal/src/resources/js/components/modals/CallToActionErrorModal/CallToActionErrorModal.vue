<template>
    <BModal
        :title="t('components.callToActionBackendErrorModal.title')"
        :ok-title="t('components.callToActionBackendErrorModal.button')"
        ok-variant="primary"
        :ok-only="true"
        @hidden="onHidden"
        ref="modal"
    >
        <p>{{ t('components.callToActionBackendErrorModal.reason') }}</p>
    </BModal>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from 'vue';
import { BModal } from 'bootstrap-vue';
import { useChoreStore } from '@/store/chore/choreStore';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import { useI18n } from 'vue-i18n-composable';

export default defineComponent({
    components: { BModal },
    setup() {
        const backendError = computed(() => useChoreStore().backendError);
        const onHidden = () => {
            useChoreStore().setBackendError(null);
            useCallToActionStore().resetTable();
        };
        const { t } = useI18n();
        const modal = ref<BModal | null>(null);
        return {
            onHidden,
            modal,
            backendError,
            t,
        };
    },
    watch: {
        backendError(newVal) {
            newVal && this.modal?.show();
        },
    },
});
</script>
