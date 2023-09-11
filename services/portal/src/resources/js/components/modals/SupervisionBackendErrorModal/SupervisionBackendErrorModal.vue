<template>
    <BModal
        :title="title"
        :ok-title="$t('components.supervisionBackendErrorModal.button')"
        ok-variant="primary"
        :ok-only="true"
        @hidden="onHidden"
        ref="modal"
    >
        <p>{{ $t('components.supervisionBackendErrorModal.reason') }}</p>
    </BModal>
</template>

<script lang="ts">
import { mapMutations } from 'vuex';
import { defineComponent } from 'vue';

import { BModal } from 'bootstrap-vue';
import { mapState } from '@/utils/vuex';

export default defineComponent({
    name: 'SupervisionBackendErrorModal',
    components: {
        BModal,
    },
    computed: {
        ...mapState('supervision', ['backendError']),
        title(): string {
            const backendErrorMessage = this.backendError?.message || '';
            return backendErrorMessage.length
                ? backendErrorMessage
                : this.$tc('components.supervisionBackendErrorModal.title');
        },
    },
    methods: {
        ...mapMutations({
            setBackendError: 'supervision/SET_BACKEND_ERROR',
            resetQuestionTable: 'supervision/RESET_QUESTION_TABLE',
        }),
        onHidden() {
            this.setBackendError(null);
            this.resetQuestionTable();
        },
    },
    watch: {
        backendError(newVal) {
            newVal && (this.$refs.modal as BModal).show();
        },
    },
});
</script>
