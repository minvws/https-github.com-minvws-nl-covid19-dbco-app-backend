<template>
    <div class="container-xl">
        <div class="row">
            <div class="col ml-5 mr-5">
                <h1 class="mt-4 mb-4 font-weight-normal d-flex align-items-end">
                    <span class="font-weight-bold">Mijn Cases</span>
                    <span class="ml-auto">
                        <BButton
                            v-if="hasPermission(PermissionV1.VALUE_caseCreate) && isAddCaseButtonUserEnabled"
                            variant="primary"
                            @click="openNewCaseForm"
                            data-testid="caseAanmaken"
                            >&#65291; Case aanmaken</BButton
                        >
                        <BButton
                            v-if="hasPermission(PermissionV1.VALUE_caseCanPickUpNew)"
                            variant="primary"
                            @click="getNewCase"
                            data-testid="caseOppakken"
                            >&#65291; Case oppakken</BButton
                        >
                    </span>
                </h1>
                <div class="mt-2">
                    <CovidCaseUserTable class="mt-4" filter="mine" ref="table" />
                </div>
            </div>
        </div>
        <FormCase ref="newcaseform" @created="onCreate" />
    </div>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import FormCase from '@/components/form/FormCase/FormCase.vue';
import CovidCaseUserTable from '@/components/utils/CovidCaseUserTable/CovidCaseUserTable.vue';
import { usePlanner } from '@/store/planner/plannerStore';
import { StoreType } from '@/store/storeType';
import { PermissionV1 } from '@dbco/enum';
import { mapGetters } from '@/utils/vuex';
import { mapWritableState } from 'pinia';
import { defineComponent } from 'vue';
import env from '@/env';

export default defineComponent({
    name: 'CovidCaseOverviewUserPage',
    components: {
        CovidCaseUserTable,
        FormCase,
    },
    data() {
        return {
            queue: 'default',
            PermissionV1: PermissionV1,
        };
    },
    computed: {
        ...mapWritableState(usePlanner, ['caseLabels']),
        ...mapGetters(StoreType.USERINFO, ['user', 'hasPermission']),
        isAddCaseButtonUserEnabled() {
            return env.isAddCaseButtonUserEnabled;
        },
    },
    async created() {
        this.caseLabels = await caseApi.getCaseLabels();
    },
    methods: {
        async getNewCase() {
            try {
                const data = await caseApi.assignNextCaseInQueue(this.queue);
                if (data?.caseUuid) {
                    window.location.href = `/editcase/${data.caseUuid}`;
                }
            } catch (error) {
                this.$modal.show({
                    title: 'Er staan geen cases voor jou in de wachtrij',
                    text: 'Op dit moment zijn er geen cases die je kunt oppakken. Neem contact op met de werkverdeler.',
                    okOnly: true,
                });
            }
        },
        onCreate(uuid: string) {
            window.location.href = `/editcase/${uuid}`;
        },
        openNewCaseForm() {
            (this.$refs.newcaseform as any).open();
        },
    },
});
</script>
