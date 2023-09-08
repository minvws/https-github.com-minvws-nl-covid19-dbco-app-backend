<template>
    <BModal :title="title" :ok-title="okTitle" ok-variant="primary" :ok-only="true" @hidden="resetModal" ref="modal">
        <p>
            {{ $tc('components.assignmentConflictModal.reason', assignmentConflicts.length) }}
        </p>
        <ul>
            <li v-for="assignmentConflict in assignmentConflicts">
                <strong>[{{ assignmentConflict.caseId }}]</strong> -
                <!-- eslint-disable-next-line vue/no-v-html : data provided by backend, no XSS risk -->
                <span v-html="assignmentConflict.assignmentStatus"></span>
            </li>
        </ul>
        <!-- eslint-disable-next-line vue/no-v-html : data provided by backend, no XSS risk -->
        <p v-html="description"></p>
    </BModal>
</template>

<script lang="ts">
import type { BModal } from 'bootstrap-vue';
import { defineComponent } from 'vue';

import type { AssignmentConflict } from '@/components/form/ts/formTypes';

export default defineComponent({
    name: 'CovidCaseAssignConflictModal',
    data() {
        return {
            description: '',
            assignmentConflicts: [] as AssignmentConflict[],
        };
    },
    computed: {
        title() {
            return `${this.$tc('components.assignmentConflictModal.title', this.assignmentConflicts.length)}`;
        },
        okTitle() {
            return `${this.$t('components.assignmentConflictModal.button')}`;
        },
    },
    methods: {
        resetModal() {
            this.description = '';
            this.assignmentConflicts = [];
        },
        show(description: string, assignmentConflicts: AssignmentConflict[]) {
            this.description = description;
            this.assignmentConflicts = assignmentConflicts;
            (this.$refs.modal as BModal).show();
        },
    },
});
</script>
