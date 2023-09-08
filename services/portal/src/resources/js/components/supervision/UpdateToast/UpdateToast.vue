<template>
    <BToast ref="toast" is-status no-auto-hide no-close-button solid toaster="b-toaster-top-center" @hidden="onHidden">
        <div class="d-flex align-items-center justify-content-sm-between">
            <p class="text-left mb-0">{{ updateMessage }}</p>
            <div class="d-flex align-items-center">
                <button type="button" aria-label="Refresh" class="refresh flex-shrink-0 mr-2" @click="onRefresh">
                    Ververs overzicht
                </button>
                <button type="button" aria-label="Close" class="close" @click="onClose">×</button>
            </div>
        </div>
    </BToast>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import { BToast } from 'bootstrap-vue';
import { mapActions, mapMutations, mapState } from '@/utils/vuex';
import { SupervisionActions } from '@/store/supervision/supervisionActions/supervisionActions';
import { SupervisionMutations } from '@/store/supervision/supervisionMutations/supervisionMutations';

export default defineComponent({
    name: 'UpdateToast',
    components: {
        BToast,
    },
    computed: {
        ...mapState('supervision', ['updateMessage']),
    },
    methods: {
        ...mapActions('supervision', {
            startPollingSupervisionQuestions: SupervisionActions.START_POLLING_SUPERVISION_QUESTIONS,
        }),
        ...mapMutations('supervision', {
            resetQuestionTable: SupervisionMutations.RESET_QUESTION_TABLE,
            setUpdateMessage: SupervisionMutations.SET_UPDATE_MESSAGE,
        }),
        onClose() {
            this.startPollingSupervisionQuestions();
            (this.$refs.toast as BToast).hide();
        },
        onHidden() {
            this.setUpdateMessage(null);
        },
        onRefresh() {
            this.resetQuestionTable();
            (this.$refs.toast as BToast).hide();
        },
    },
    watch: {
        updateMessage(newVal) {
            newVal && (this.$refs.toast as BToast).show();
        },
    },
});
</script>

<style lang="scss" scoped>
.refresh {
    background-color: transparent;
    color: inherit;
    font-weight: 500;
    outline: none;
    box-shadow: none;
    border: none;
}
</style>
