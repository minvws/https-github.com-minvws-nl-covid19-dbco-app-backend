<template>
    <FullScreenModal
        v-if="loaded"
        @onClose="closeModal()"
        :path="['Contact binnen besmettelijke periode', taskFragments.general.label]"
    >
        <!-- eslint-disable-next-line vuejs-accessibility/no-static-element-interactions -->
        <template v-slot:header @click="closeModal" @keyup.enter="closeModal">
            <span v-if="meta">
                {{ meta.caseId }} /
                <strong>{{ meta.name }}</strong>
            </span>
        </template>
        <template v-slot:action>
            <div class="contact-editing-modal-action">
                <LastUpdated />
                <BDropdown
                    class="m-md-2 dbco-select"
                    variant="link"
                    v-if="taskFragments.inform.status"
                    :disabled="!hasTaskEditPermission"
                >
                    <template #button-content>
                        <span data-testid="inform-status">
                            <span
                                :class="[
                                    'icon',
                                    `icon--status-${getContentForStatus(taskFragments.inform.status).icon}`,
                                ]"
                            >
                            </span
                            >{{ getContentForStatus(taskFragments.inform.status).label }}
                        </span>
                    </template>
                    <BDropdownItem
                        v-for="status in Object.values(InformStatusV1)"
                        :key="status"
                        @click="updateInformStatus(status)"
                    >
                        <div>
                            <span :class="['icon', `icon--status-${getContentForStatus(status).icon}`]"></span
                            >{{ getContentForStatus(status).label }}
                        </div>
                    </BDropdownItem>
                </BDropdown>
                <BButton block variant="primary" @click="closeModal()" class="text-nowrap">Terug naar index</BButton>
            </div>
        </template>
        <template v-slot:sidebar>
            <CovidCaseSidebar :schema="$as.any(schemaSidebar())" />
        </template>
        <FormRenderer :rules="$as.defined(rootSchema).rules.task" :schema="schema()" storeType="task" />
    </FullScreenModal>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import CovidCaseSidebar from '@/components/caseEditor/CovidCaseSidebar/CovidCaseSidebar.vue';
import LastUpdated from '@/components/caseEditor/LastUpdated/LastUpdated.vue';
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import { getRootSchema, getSchema } from '@/components/form/ts/formSchema';
import { mapGetters } from 'vuex';
import { PermissionV1, InformStatusV1 } from '@dbco/enum';
import type { Schema } from '@/components/form/ts/schemaType';
import { StoreType } from '@/store/storeType';
import type { TaskStoreState } from '@/store/task/taskStore';

interface Data {
    loaded: boolean;
    InformStatusV1: typeof InformStatusV1;
    rootSchema?: Schema | null;
}

export default defineComponent({
    name: 'ContactEditingModal',
    components: { FullScreenModal, CovidCaseSidebar, LastUpdated },
    props: {
        contact: { type: Object as PropType<TaskStoreState> },
    },
    data() {
        return {
            loaded: false,
            InformStatusV1: InformStatusV1,
            rootSchema: undefined,
        } as Data;
    },
    async created() {
        await this.$store.dispatch('task/LOAD', this.selectedTaskUuid);
        await this.$store.commit('task/CHANGE', {
            path: 'uuid',
            values: this.selectedTaskUuid,
        });

        this.rootSchema = getRootSchema();
        this.loaded = true;
    },
    async destroyed() {
        await this.$store.dispatch(`${StoreType.TASK}/FETCH_TASKS`, this.caseUuid);
        await this.emptySelectedTask();
    },
    computed: {
        // eslint-disable-next-line no-warning-comments
        // TODO: Cast mapGetters to resolve correct types
        // mapRootGetters
        ...mapGetters({
            meta: `${StoreType.INDEX}/meta`,
            caseUuid: `${StoreType.INDEX}/uuid`,
            taskFragments: `${StoreType.TASK}/fragments`,
        }),
        hasTaskEditPermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_taskEdit);
        },
        selectedTaskUuid() {
            return this.$store.getters[`${StoreType.TASK}/selectedTaskUuid`];
        },
        taskRules() {
            return this.rootSchema?.rules.task;
        },
    },

    methods: {
        schema: () => getSchema('contact-modal-contagious'),
        schemaSidebar: () => getSchema('contact-modal-contagious-sidebar'),
        getContentForStatus: (status: string) => {
            switch (status) {
                case InformStatusV1.VALUE_uninformed:
                    return { icon: 'uninformed', label: 'Nog niet geïnformeerd' };
                case InformStatusV1.VALUE_unreachable:
                    return { icon: 'unreachable', label: 'Geen gehoor' };
                case InformStatusV1.VALUE_emailSent:
                    return { icon: 'email-sent', label: 'Alleen gemaild' };
                case InformStatusV1.VALUE_informed:
                    return { icon: 'informed', label: 'Geïnformeerd' };
            }

            return { icon: '', label: 'Onbekend' };
        },
        updateInformStatus(status: InformStatusV1) {
            return this.$store.dispatch(`task/UPDATE_FORM_VALUE`, {
                inform: { ...this.taskFragments.inform, status: status },
            });
        },
        async closeModal() {
            this.$emit('onClose');
            await this.emptySelectedTask();
        },
        async emptySelectedTask() {
            await this.$store.commit(`${StoreType.TASK}/EMPTY_SELECTED_TASK_UUID`);
        },
    },
});
</script>

<style lang="scss">
.contact-editing-modal-action {
    display: flex;
    flex-direction: row;
    align-items: center;

    .dropdown-menu {
        width: 230px;

        li {
            padding: 8px;
            font-size: 0.875rem;
        }
    }
}
</style>
