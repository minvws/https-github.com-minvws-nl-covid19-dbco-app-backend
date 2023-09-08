<template>
    <article v-if="selectedQuestion" id="expert-question">
        <div>
            <header>
                <h4>{{ selectedQuestion.subject }}</h4>
            </header>
            <p>{{ selectedQuestion.question }}</p>
            <footer>
                <p>{{ enquirerInfo }}</p>
                <small>{{ dateAsked }}</small>
            </footer>
        </div>
        <div id="expert-question-answer" v-if="!!selectedQuestion.answer">
            <header>
                <h4>{{ $t('components.answerSidebar.answer_title') }}</h4>
            </header>
            <p>{{ selectedQuestion.answer.value }}</p>
            <footer>
                <p>{{ answeredByInfo }}</p>
                <small>{{ dateAnswered }}</small>
            </footer>
        </div>
    </article>
</template>

<script lang="ts">
import { parseDate, formatDate } from '@/utils/date';

import { defineComponent } from 'vue';
import { mapState } from '@/utils/vuex';

export default defineComponent({
    name: 'ExpertQuestion',
    computed: {
        ...mapState('supervision', ['selectedQuestion']),
        dateAnswered(): string {
            return this.selectedQuestion?.answer?.createdAt
                ? formatDate(parseDate(this.selectedQuestion.answer.createdAt), 'd MMMM yyyy HH:mm')
                : this.$tc('components.question.no_creation_date');
        },
        dateAsked(): string {
            return this.selectedQuestion?.createdAt
                ? formatDate(parseDate(this.selectedQuestion.createdAt), 'd MMMM yyyy HH:mm')
                : this.$tc('components.question.no_creation_date');
        },
        enquirerInfo(): string {
            const name = this.selectedQuestion?.user?.name;
            const roles = this.selectedQuestion?.user?.roles;
            const translatedRoles = roles ? roles.map((role) => this.$tc(`roles.${role}`)).join(', ') : null;
            const phone = this.selectedQuestion?.phone;
            return (
                [name, translatedRoles, phone].filter(Boolean).join(', ') ||
                this.$tc('components.question.no_user_info')
            );
        },
        answeredByInfo(): string {
            const user = this.selectedQuestion?.answer?.answeredBy;
            if (!user) return this.$tc('components.question.no_user_info');
            const translatedRoles = user.roles.map((role) => this.$t(`roles.${role}`)).join(', ');
            return [user.name, translatedRoles].filter(Boolean).join(', ');
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#expert-question {
    padding: 2rem;

    #expert-question-answer {
        border-top: $border-default;
        margin-top: $padding-default;
        padding-top: $padding-default;
    }

    header {
        margin-bottom: $padding-sm;
    }

    h4 {
        font-size: 1.125rem;
        line-height: 1.333;
        word-break: break-word;
    }

    p {
        margin-bottom: $padding-sm;
        word-break: break-word;
    }

    footer {
        line-height: 1;

        p {
            font-weight: 500;
        }

        small {
            color: $dark-grey;
            font-size: 0.75rem;
        }
    }
}
</style>
