<template>
    <ChoreSidebar
        :hint="hint ? $tc(`components.questionSidebar.${hint}`) : undefined"
        :title="$tc(`components.questionSidebar.${questionType}`)"
    >
        <template #header>
            <button type="button" id="deselect" @click="deselectQuestion" v-if="selectedQuestion">
                <i class="icon icon--close icon--lg" aria-hidden="true"></i>
                <span style="display: none">{{ $t('components.questionSidebar.close_question') }}</span>
            </button>
        </template>
        <template #default>
            <ExpertQuestion />
            <ExpertForm v-if="selectedQuestion" />
        </template>
    </ChoreSidebar>
</template>

<script lang="ts">
import { mapMutations } from 'vuex';

import { defineComponent } from 'vue';

import ChoreSidebar from '@/components/chore/ChoreSidebar/ChoreSidebar.vue';
import ExpertQuestion from '@/components/supervision/ExpertQuestion/ExpertQuestion.vue';
import ExpertForm from '@/components/supervision/ExpertForm/ExpertForm.vue';
import { mapRootGetters, mapState } from '@/utils/vuex';

export default defineComponent({
    name: 'QuestionSidebar',
    components: {
        ChoreSidebar,
        ExpertQuestion,
        ExpertForm,
    },
    computed: {
        ...mapRootGetters({
            expertQuestionAnsweredByUser: 'supervision/expertQuestionAnsweredByUser',
        }),
        ...mapState('supervision', ['selectedQuestion']),
        questionType() {
            return !this.selectedQuestion?.uuid ? 'no_question_selected' : `question_for_${this.selectedQuestion.type}`;
        },
        hint() {
            if (this.expertQuestionAnsweredByUser) return;
            if (!this.selectedQuestion?.uuid) return 'no_question_selected_hint';
        },
    },
    methods: {
        ...mapMutations({ deselectQuestion: 'supervision/DESELECT_QUESTION' }),
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#expert-question {
    flex: 1 1 auto;
    overflow-y: auto;
}
</style>
