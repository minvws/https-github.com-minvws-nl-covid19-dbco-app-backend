<template>
    <div>
        <BTableSimple class="table-ggd table--clickable w-100" v-if="loaded">
            <colgroup>
                <col class="w-30" />
                <col class="w-20" />
                <col class="w-20" />
                <col class="w-30" />
            </colgroup>
            <BThead>
                <BTr>
                    <BTh scope="col">Naam en toelichting</BTh>
                    <BTh scope="col">Laatste contact</BTh>
                    <BTh scope="col">Geïnformeerd</BTh>
                    <BTh scope="col">Gegevens</BTh>
                </BTr>
            </BThead>
            <BTbody>
                <BTr
                    v-for="(task, $index) in tasks"
                    :key="$index"
                    @click="editContact(task)"
                    :class="['task', task.accessible ? 'task-accessible' : 'task-inaccessible']"
                >
                    <BTd v-if="task.accessible">
                        <span v-if="task.derivedLabel">
                            <strong>{{ task.derivedLabel }}</strong>
                        </span>
                        <span v-else>
                            <strong>{{ task.label }}</strong>
                        </span>
                        <br /><span class="task-subtitle">{{ task.taskContext }}</span>
                    </BTd>
                    <BTd v-else class="py-3">
                        <strong>Incubatieperiode voorbij - geen besmetting bekend</strong>
                    </BTd>
                    <BTd>
                        <strong>{{ $filters.dateFormatLong(task.dateOfLastExposure) }}</strong>
                        <br /><span class="task-subtitle">{{ $filters.categoryFormat(task.category) }}</span>
                    </BTd>
                    <BTd>
                        <span>{{ informStatus[task.informStatus] }}</span>
                    </BTd>
                    <BTd class="td--data-status">
                        <div class="d-flex justify-content-between align-items-center">
                            <div v-if="task.progress == 'complete'">
                                <img :src="check100Svg" class="mr-2" aria-hidden="true" alt="" />
                                Gegevens compleet
                            </div>
                            <div v-else-if="task.progress == 'contactable'">
                                <img :src="check50Svg" class="mr-2" aria-hidden="true" alt="" />
                                Voldoende gegevens
                            </div>
                            <div role="alert" v-else>
                                <img :src="checkWarnSvg" class="mr-2" aria-hidden="true" alt="" />
                                Gegevens incompleet
                            </div>
                            <div class="d-flex justify-content-end align-items-center">
                                <i class="icon icon--chevron-right task-chevron ml-2"></i>
                            </div>
                        </div>
                    </BTd>
                </BTr>
            </BTbody>
            <ContactEditingModal v-if="selectedTaskUuid" />
        </BTableSimple>
        <div v-else class="mb-5 text-center">
            <BSpinner variant="primary" small />
        </div>
    </div>
</template>

<script>
import ContactEditingModal from '@/components/modals/ContactEditingModal/ContactEditingModal.vue';
import { informStatusV1Options, PermissionV1 } from '@dbco/enum';

import { StoreType } from '@/store/storeType';

import check100Svg from '@images/check-100.svg';
import check50Svg from '@images/check-50.svg';
import checkWarnSvg from '@images/check-warn.svg';

export default {
    name: 'ContactSummaryTable',
    components: { ContactEditingModal },
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            loaded: false,
            informStatus: informStatusV1Options,
            check100Svg,
            check50Svg,
            checkWarnSvg,
        };
    },
    async created() {
        await this.loadContacts(this.caseUuid);
    },
    watch: {
        caseUuid: function (newVal) {
            this.loadContacts(newVal);
        },
    },
    computed: {
        hasTaskEditPermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_taskEdit);
        },
        tasks() {
            return this.$store.getters[`${StoreType.TASK}/tasks`];
        },
        selectedTaskUuid() {
            return this.$store.getters[`${StoreType.TASK}/selectedTaskUuid`];
        },
    },
    methods: {
        async loadContacts(caseUuid) {
            await this.$store.dispatch(`${StoreType.TASK}/FETCH_TASKS`, caseUuid);
            this.loaded = true;
        },
        async editContact(task) {
            await this.$store.commit(`${StoreType.TASK}/SET_SELECTED_TASK_UUID`, task);
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.task {
    &.task-accessible {
        cursor: pointer;
    }

    &-subtitle {
        color: $light-grey;
    }
}

.disabled-copy-button-tooltip {
    margin-right: 1.7rem;
}

.btn-outline-primary.disabled-copy-button:hover .icon {
    filter: none;
}

.task-chevron {
    color: $input-grey;
    margin: 0;
    height: 1.25rem;
    width: 1.25rem;
    padding-left: 0.75rem;
}
</style>
