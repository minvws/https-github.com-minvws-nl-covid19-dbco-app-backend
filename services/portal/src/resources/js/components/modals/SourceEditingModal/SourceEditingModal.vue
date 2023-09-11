<template>
    <FullScreenModal v-if="loaded" v-model="isOpen" :path="['Bronpersonen', $as.defined(contact).label]">
        <template v-slot:header>
            <span v-if="meta">
                {{ meta.caseId }} /
                <strong>{{ meta.name }}</strong>
            </span>
        </template>
        <template v-slot:action>
            <BButton variant="primary" @click="isOpen = false" class="w-auto">Terug naar index</BButton>
        </template>
        <template v-slot:sidebar>
            <CovidCaseSidebar :schema="schemaSidebar()" />
        </template>
        <FormRenderer :rules="rules()" :schema="schema()" storeType="task" />
    </FullScreenModal>
</template>

<script lang="ts">
import CovidCaseSidebar from '@/components/caseEditor/CovidCaseSidebar/CovidCaseSidebar.vue';
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import { getRootSchema, getSchema } from '@/components/form/ts/formSchema';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Task } from '@dbco/portal-api/task.dto';

export default defineComponent({
    name: 'SourceEditingModal',
    components: { FullScreenModal, CovidCaseSidebar },
    props: {
        contact: {
            type: Object as PropType<Task>,
        },
    },
    data() {
        return {
            loaded: false,
            isOpen: Boolean(this.contact),
            selectedContact: this.contact,
        };
    },
    async created() {
        this.taskStore = { path: 'uuid', values: this.contact?.uuid };

        await this.$store.dispatch('task/LOAD', this.contact?.uuid);
        this.loaded = true;
    },
    destroyed() {
        void this.$store.dispatch('task/CLEAR');
    },
    computed: {
        meta() {
            return this.$store.getters['index/meta'];
        },
        taskStore: {
            get() {
                return this.$store.getters['task/fragments'];
            },
            set(payload: any) {
                void this.$store.dispatch('task/CHANGE', payload);
            },
        },
    },
    methods: {
        schema: () => getSchema('contact-modal-source'),
        schemaSidebar: () => getSchema('contact-modal-source-sidebar') as any,
        rules: () => getRootSchema()?.rules.task ?? null,
    },
    watch: {
        isOpen: function (newVal) {
            if (!newVal) {
                this.$emit('onClose');
            }
        },
    },
});
</script>

<style scoped></style>
