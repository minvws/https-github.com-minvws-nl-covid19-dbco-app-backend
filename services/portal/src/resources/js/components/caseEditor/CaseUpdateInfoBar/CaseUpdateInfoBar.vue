<template>
    <div v-if="caseUpdate">
        <div class="info-block info-block--info form-font">
            <InfoIcon class="svg-icon" />
            <span class="flex-grow-1 px-2">
                <strong>Let op:</strong> De index heeft
                {{ $filters.dateFnsFormat(caseUpdate.receivedAt, `dd MMM 'om' H:mm`) }} antwoorden aangeleverd via
                zelfbco.nl. Bekijk de antwoorden en kies welke je wilt overnemen.
            </span>
            <BButton variant="link" size="sm" v-b-modal.CaseUpdateModal @click="showModal = true">Bekijken ></BButton>
        </div>
        <CaseUpdateModal
            v-if="showModal"
            :caseUpdateId="caseUpdate.uuid"
            @submitted="casedUpdated"
            @hide="showModal = false"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { caseUpdateApi } from '@dbco/portal-api';
import type { CaseUpdateResponseItem } from '@dbco/portal-api/caseUpdate.dto';
import CaseUpdateModal from '@/components/modals/CaseUpdateModal/CaseUpdateModal.vue';
import { mapRootGetters } from '@/utils/vuex';
import InfoIcon from '@icons/info.svg?vue';
export default defineComponent({
    name: 'CaseUpdateInfoBar',
    components: { CaseUpdateModal, InfoIcon },
    props: {},
    data() {
        return {
            isLoading: true,
            caseUpdate: undefined as CaseUpdateResponseItem | undefined,
            showModal: false,
        };
    },
    async created() {
        const { total, items } = await caseUpdateApi.listCaseUpdates(this.uuid);
        if (total > 0) {
            this.caseUpdate = items[0];
        }
        this.isLoading = false;
    },
    computed: {
        ...mapRootGetters({ uuid: 'index/uuid' }),
    },
    methods: {
        casedUpdated() {
            this.caseUpdate = undefined;
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.info-block {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 20px;
    background: rgba($yellow, 0.5);
    color: $black;
    padding: 0.875rem 3rem;
    cursor: pointer;

    .svg-icon {
        height: 1rem;
        flex-shrink: 0;
    }

    .btn-link {
        color: $black;
        font-weight: bold;
    }

    .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba($black, 0.5);
    }
}
</style>
