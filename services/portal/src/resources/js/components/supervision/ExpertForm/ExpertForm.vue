<template>
    <BForm id="expert-form" v-if="!expertQuestionAnsweredByUser">
        <div v-if="expertQuestionPickedUpByUser" id="expert-form-textarea">
            <BFormTextarea
                min="0"
                rows="6"
                v-model="answer"
                :maxlength="maxLength"
                :placeholder="$t('components.answerSidebar.answer_placeholder')"
                @input="showRequiredMessage = false"
            />
            <small>{{ characterCount }}</small>
            <div class="mt-3 d-flex" v-if="showRequiredMessage">
                <i class="icon icon--error-notice flex-shrink-0 ml-0 mr-2" />
                <p class="form-font m-0">
                    {{ $t('components.answerSidebar.answer_required_message') }}
                </p>
            </div>
        </div>

        <ChoreActions
            :labelForDropAction="$tc('components.questionSidebar.drop_question')"
            :labelForPickupAction="$tc('components.questionSidebar.pick_up_question')"
            :labelForTertiaryAction="$tc('components.questionSidebar.answer_question')"
            :labelForViewLink="$tc(`components.questionSidebar.view`)"
            :pickedUp="expertQuestionPickedUpByUser"
            :viewLink="`/editcase/${$as.defined(selectedQuestion).caseUuid}`"
            @tertiaryAction="submitAnswer"
            @toggle="togglePickup"
        />
    </BForm>
    <div id="expert-form" v-else>
        <BButton variant="secondary" type="button" disabled>
            {{ $tc('components.answerSidebar.answer_answered_label') }}
        </BButton>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ChoreActions from '@/components/chore/ChoreActions/ChoreActions.vue';
import { mapState, mapRootGetters, mapActions } from '@/utils/vuex';
import { SupervisionActions } from '@/store/supervision/supervisionActions/supervisionActions';

export default defineComponent({
    name: 'ExpertForm',
    components: {
        ChoreActions,
    },
    data() {
        return {
            answer: '',
            maxLength: 5000,
            showRequiredMessage: false,
        };
    },
    computed: {
        ...mapState('supervision', ['selectedQuestion']),
        ...mapRootGetters({
            expertQuestionAnsweredByUser: 'supervision/expertQuestionAnsweredByUser',
            expertQuestionPickedUpByUser: 'supervision/expertQuestionPickedUpByUser',
        }),
        characterCount() {
            return `${this.answer.length}/${this.maxLength}`;
        },
        answerFilled() {
            return !!this.answer && this.answer !== '';
        },
    },
    methods: {
        ...mapActions('supervision', {
            answerQuestion: SupervisionActions.ANSWER_QUESTION,
            dropQuestion: SupervisionActions.DROP_QUESTION,
            pickupQuestion: SupervisionActions.PICK_UP_QUESTION,
        }),
        submitAnswer() {
            if (!this.answerFilled) {
                this.showRequiredMessage = true;
            } else {
                void this.answerQuestion(this.answer);
            }
        },
        togglePickup() {
            if (!this.selectedQuestion?.uuid) return;
            this.showRequiredMessage = false;
            this.expertQuestionPickedUpByUser
                ? void this.dropQuestion(this.selectedQuestion?.uuid)
                : void this.pickupQuestion(this.selectedQuestion?.uuid);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#expert-form {
    border-top: $border-default;

    #expert-form-textarea {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding: $padding-default;
        padding-bottom: $padding-sm;

        textarea {
            flex: 1 0 auto;
            margin-bottom: 0;
            resize: none;
        }

        small {
            color: $dark-grey;
            font-size: 0.75rem;
        }
    }

    button:disabled {
        margin: 1rem 2rem 1rem auto;
    }
}
</style>
