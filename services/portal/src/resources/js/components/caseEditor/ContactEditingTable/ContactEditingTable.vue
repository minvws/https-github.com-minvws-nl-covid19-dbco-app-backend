<template>
    <div v-if="isLoaded">
        <BTableSimple class="table-form table-ggd table--clickable w-100">
            <colgroup>
                <col :class="isContactGroup ? 'w-35' : 'w-70'" />
                <col class="w-35" v-if="isContactGroup" />
                <col class="w-20" />
                <col class="w-5" />
                <col class="w-10" v-if="isContactGroup" />
                <col class="w-10" v-if="!isContactGroup" />
                <col class="td-icon" />
            </colgroup>
            <BThead>
                <BTr>
                    <BTh scope="col">Naam<i class="icon icon--eye"></i></BTh>
                    <BTh scope="col" v-if="isContactGroup">Notitie (optioneel)<i class="icon icon--eye"></i></BTh>
                    <BTh scope="col">Laatste contact</BTh>
                    <BTh scope="col">Categorie</BTh>
                    <BTh scope="col" v-if="isContactGroup">Wie informeert</BTh>
                    <BTh scope="col" v-if="!isContactGroup" class="cell-flex">
                        Bron
                        <i
                            class="icon icon--questionmark"
                            v-b-tooltip.hover
                            title="Vink hier aan welke bron of bronnen het meest waarschijnlijk zijn."
                        />
                    </BTh>
                    <BTh scope="col"></BTh>
                </BTr>
            </BThead>
            <BTbody>
                <ContactEditingTableRow
                    v-for="(task, $index) in tableRows"
                    :key="`task-${$index}`"
                    @change="(task) => debouncedPersist(task, $index)"
                    @delete="(uuid) => deleteTask(uuid)"
                    @click="(uuid, targetEl) => checkTableRowClick(uuid, targetEl)"
                    :errors="validationErrors[task.uuid || '']"
                    :isSaving="savingUuids.includes(task.uuid || '')"
                    :task="$as.any(task)"
                />
            </BTbody>
        </BTableSimple>
        <ContactEditingModal v-if="selectedTaskUuid && isContactGroup" @onClose="onModalClose" />
        <SourceEditingModal
            v-if="selectedTask && selectedTaskUuid && !isContactGroup"
            :contact="selectedTask"
            @onClose="onModalClose"
        />
    </div>
    <div v-else class="mb-5 text-center">
        <BSpinner variant="primary" small />
    </div>
</template>

<script lang="ts">
import { taskApi } from '@dbco/portal-api';
import { informedByV1Options } from '@dbco/enum';
import { getAllErrors } from '@/components/form/ts/formRequest';
import ContactEditingModal from '@/components/modals/ContactEditingModal/ContactEditingModal.vue';
import SourceEditingModal from '@/components/modals/SourceEditingModal/SourceEditingModal.vue';
import { SharedActions } from '@/store/actions';
import { StoreType } from '@/store/storeType';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import axios from 'axios';
import _ from 'lodash';
import ContactEditingTableRow from './ContactEditingTableRow/ContactEditingTableRow.vue';
import { mapActions, mapRootGetters } from '@/utils/vuex';
import { TaskMutations } from '@/store/task/taskMutations';
import type { Task } from '@dbco/portal-api/task.dto';
import { TaskGroup } from '@dbco/portal-api/task.dto';
import type { ValidationResult } from '@dbco/portal-api/validation-result.dto';

interface Data {
    communicationOptions: typeof informedByV1Options;
    isLoaded: boolean;
    savingUuids: string[];
    selectedTask?: Task;
    tasks: Task[];
    validationErrors: AnyObject;
}

export default defineComponent({
    name: 'ContactEditingTable',
    components: {
        ContactEditingTableRow,
        ContactEditingModal,
        SourceEditingModal,
    },
    props: {
        group: {
            type: String as PropType<`${TaskGroup}`>,
            required: true,
            validator: (prop: TaskGroup) =>
                [TaskGroup.Contact, TaskGroup.PositiveSource, TaskGroup.SymptomaticSource].includes(prop),
        },
    },
    data() {
        return {
            communicationOptions: informedByV1Options,
            isLoaded: false,
            savingUuids: [],
            selectedTask: undefined,
            tasks: [],
            validationErrors: {},
        } as Data;
    },
    async created() {
        await this.load(this.caseUuid);
    },
    computed: {
        ...mapRootGetters({
            caseUuid: `${StoreType.INDEX}/uuid`,
            storeTasks: `${StoreType.INDEX}/tasks`,
            selectedTaskUuid: `${StoreType.TASK}/selectedTaskUuid`,
        }),
        isContactGroup() {
            return this.group === TaskGroup.Contact;
        },
        tableRows() {
            return [
                ...this.tasks,
                // plus placeholder for new contact
                {
                    accessible: true,
                    dateOfLastExposure: '',
                    group: this.group,
                    uuid: '',
                },
            ];
        },
    },
    watch: {
        tasks: {
            async handler(newVal) {
                await this.indexChangeAction({
                    path: 'tasks',
                    values: {
                        ...this.storeTasks,
                        [this.group]: newVal,
                    },
                });
            },
            deep: true,
        },
    },
    methods: {
        ...mapActions(StoreType.INDEX, { indexChangeAction: SharedActions.CHANGE }),
        debouncedPersist: _.debounce(async function (this: any, task, index) {
            await this.persist(task, index);
        }, 300),
        checkTableRowClick(uuid: string, eventTarget?: HTMLElement) {
            // If event target element is passed
            // Check if the user clicked on a table column/row or BInputGroup
            if (
                eventTarget &&
                !['TD', 'TR'].includes(eventTarget.nodeName) &&
                !eventTarget.classList.contains('input-group')
            ) {
                return;
            }

            this.edit(uuid);
        },
        deleteTask(uuid?: string) {
            if (!uuid) return;

            this.$modal.show({
                title: 'Weet je zeker dat je dit contact wilt verwijderen?',
                text: 'Let op: je kunt dit hierna niet meer ongedaan maken',
                okTitle: 'Verwijderen',
                okVariant: 'outline-danger',
                onConfirm: async () => {
                    this.savingUuids.push(uuid);
                    try {
                        // API requests to fetch data should never be called in components
                        // In the future a dispatch should be called here for deleting the task
                        // Jira ticket: DBCO-4766
                        await taskApi.deleteTask(uuid);
                        this.tasks = this.tasks.filter((taskItem) => taskItem.uuid !== uuid);
                    } finally {
                        this.savingUuids = this.savingUuids.filter((uuidItem) => uuidItem !== uuid);
                    }
                },
            });
        },
        edit(uuid: string) {
            const taskToEdit = this.tasks.find((task) => task.uuid === uuid);
            if (!taskToEdit?.uuid || !taskToEdit?.accessible) return;

            // Beware, in the future <SourceEditingModal> hopefully will use the store and this line will be obsolete
            this.selectedTask = taskToEdit;
            this.$store.commit(`${StoreType.TASK}/${TaskMutations.SET_SELECTED_TASK_UUID}`, taskToEdit);
        },
        handleValidation(uuid: string, validationResult: ValidationResult, isError = false) {
            const validationErrors = getAllErrors(validationResult);

            // If request is unsuccessful and there are no validation errors
            if (isError && !validationErrors) {
                alert('Er ging iets mis bij het opslaan van de nieuwe contactpersoon');
                return;
            }

            this.validationErrors[uuid] = validationErrors ? Object.keys(validationErrors.errors) : [];
        },
        async load(caseUuid: string) {
            if (!caseUuid) return;
            // In the future this.tasks should be replaced by a computed property pointed to the store
            // API requests to fetch data should never be called in components
            // Jira ticket: DBCO-4766
            const { tasks } = await taskApi.getTasks(caseUuid, this.group);
            this.tasks = tasks;
            this.isLoaded = true;
        },
        async onModalClose() {
            this.selectedTask = undefined;
            this.$store.commit(`${StoreType.TASK}/${TaskMutations.EMPTY_SELECTED_TASK_UUID}`);
            await this.load(this.caseUuid);
        },
        async persist(task: Task, index: number) {
            // Ensure we have the latest uuid to prevent creating duplicates
            task.uuid = this.tableRows[index].uuid ?? '';

            // If this row is new, but already saving, debounce the persist call so it will update after creation
            if (!task.uuid && this.savingUuids.includes(task.uuid)) {
                void this.debouncedPersist(task, index);
                return;
            }

            this.savingUuids.push(task.uuid);

            try {
                // These api calls need to be replaced by store functions
                // Jira ticket: DBCO-4766
                const data: { task: Task; validationResult: any } = await (!task.uuid
                    ? taskApi.createTask(this.caseUuid, task)
                    : taskApi.updateTask(task.uuid, task));

                this.validationErrors[task.uuid] = [];

                const updatedTask: Partial<Task> = {
                    ...(this.tableRows[index] as Task),
                    uuid: data.task.uuid,
                    category: data.task.category,
                    communication: data.task.communication,
                };

                this.$set(this.tasks, index, updatedTask);

                this.handleValidation(data.task.uuid, data.validationResult);
            } catch (error) {
                const validationResult = axios.isAxiosError(error) ? error.response?.data?.validationResult : {};
                this.handleValidation(task.uuid, validationResult, true);
            } finally {
                this.savingUuids = this.savingUuids.filter((uuid) => uuid !== task.uuid);
            }
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.cell-flex {
    display: flex;
    align-items: center;

    .icon {
        margin-left: $padding-xs;
        margin-bottom: 2px;
    }
}
</style>
