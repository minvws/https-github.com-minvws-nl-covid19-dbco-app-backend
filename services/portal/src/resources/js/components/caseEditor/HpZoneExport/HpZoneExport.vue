<template>
    <div>
        <!-- eslint-disable-next-line vue/no-v-html : data provided by backend, no XSS risk  -->
        <div v-html="diagnosticsTableHtml" class="diagnostics-table"></div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { copyApi } from '@dbco/portal-api';

interface Data {
    diagnosticsTableHtml: string;
}

export default defineComponent({
    name: 'HpZoneExport',
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            diagnosticsTableHtml: '',
        } as Data;
    },
    created() {
        void this.loadDiagnosticsTable();
    },
    computed: {
        communication() {
            return this.$store.getters['index/fragments'].communication;
        },
    },
    methods: {
        async loadDiagnosticsTable() {
            if (!this.caseUuid) return;

            const data = await copyApi.getDiagnostics(this.caseUuid);
            this.diagnosticsTableHtml = data;
        },
    },
    watch: {
        communication() {
            void this.loadDiagnosticsTable();
        },
    },
});
</script>

<style scoped lang="scss">
@import '@/../scss/variables';

.diagnostics-table {
    &::v-deep table {
        width: 100%;
        font-weight: 500;
        border: 0;

        tr {
            border: 0;
            border-bottom: 1px solid $lightest-grey;
            td {
                padding: 1rem 0;
                border: 0;

                &:first-of-type {
                    width: 30%;
                    padding-right: 2rem;
                    font-weight: normal;
                }
            }
        }
    }
}
</style>
