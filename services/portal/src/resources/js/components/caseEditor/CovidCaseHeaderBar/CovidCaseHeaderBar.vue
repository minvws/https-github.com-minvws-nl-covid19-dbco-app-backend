<template>
    <div class="header-bar bg-white pt-4">
        <div class="wrapper px-3 px-xl-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap flex-md-nowrap gap-3">
                <h2
                    v-if="covidCase.uuid"
                    class="d-flex flex-wrap text-nowrap mb-0 gap-x-3 gap-y-2"
                    data-testid="text-covidCase"
                >
                    <div class="d-flex align-items-center flex-nowrap title-container">
                        <div class="font-weight-normal" data-testid="text-organisation">
                            {{
                                covidCase.meta.organisation && !$as.any(covidCase.meta.organisation).isCurrent
                                    ? `${covidCase.meta.organisation.abbreviation}-${covidCase.meta.caseId}`
                                    : covidCase.meta.caseId
                            }}
                            <span class="mx-2">/</span>
                        </div>
                        {{ covidCase.meta.name }}
                    </div>
                    <div class="d-flex gap-2">
                        <BBadge
                            v-if="covidCase.meta.organisation && !$as.any(covidCase.meta.organisation).isCurrent"
                            variant="outline-light-grey"
                            class="mr-2"
                            data-testid="badge-organisation"
                        >
                            <i class="icon icon--clusters icon--center ml-0" />
                            {{ covidCase.meta.organisation.name }}
                        </BBadge>
                        <DbcoPhaseDropdown
                            :bcoPhase="$as.any(covidCase.meta).bcoPhase"
                            :cases="[$as.any(covidCase.meta).uuid]"
                            @phaseChanged="$emit('phaseChanged')"
                            toggleClass="badge text-primary border"
                            data-testid="dropdown-phase"
                            :disabled="!userCanEdit"
                        />
                    </div>
                </h2>
                <div class="d-flex align-items-center text-nowrap justify-content-end flex-fill">
                    <LastUpdated />
                    <BButton
                        v-if="userCanEdit"
                        class="w-auto ml-2 action-button"
                        :href="`/editcase/${covidCase.uuid}/tasks/new`"
                        :disabled="!userCanEdit"
                        variant="outline-primary"
                        data-testid="create-call-to-action-button"
                    >
                        Taak aanmaken
                    </BButton>
                    <BButton
                        v-if="userCanEdit"
                        class="w-auto ml-2 action-button"
                        variant="outline-primary"
                        :disabled="!userCanEdit"
                        @click="$bvModal.show('supervision-modal')"
                        data-testid="supervision-question-button"
                    >
                        Hulp vragen
                    </BButton>
                    <BDropdown
                        size="md"
                        right
                        text="Status updaten"
                        variant="primary"
                        :disabled="!userCanEdit"
                        v-if="hasCaseEditContactStatusPermission"
                        data-testid="edit-case-status-dropdown"
                        class="ml-2"
                    >
                        <BDropdownItem @click="eventBus.$emit('open-osiris-modal')"
                            >Afronden of teruggeven</BDropdownItem
                        >
                    </BDropdown>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import type { IndexStoreState } from '@/store/index/indexStore';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import DbcoPhaseBadge from '@/components/utils/DbcoPhaseBadge/DbcoPhaseBadge.vue';
import DbcoPhaseDropdown from '@/components/utils/DbcoPhaseDropdown/DbcoPhaseDropdown.vue';
import LastUpdated from '../LastUpdated/LastUpdated.vue';
import { PermissionV1 } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import { useEventBus } from '@/composables/useEventBus';

export default defineComponent({
    name: 'CovidCaseHeaderBar',
    components: {
        DbcoPhaseBadge,
        LastUpdated,
        DbcoPhaseDropdown,
    },
    props: {
        covidCase: {
            type: Object as PropType<IndexStoreState>,
            required: true,
        },
    },
    computed: {
        hasCaseEditContactStatusPermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_caseEditContactStatus);
        },
        userCanEdit,
        eventBus() {
            return useEventBus();
        },
    },
});
</script>
<style lang="scss" scoped>
@import '@/../scss/variables';
.header-bar {
    transition: padding-right $sidebar-transition;

    @media (min-width: 1920px) {
        padding-right: $sidebar-width;

        &.sidebar-collapsed {
            padding-right: 0;
        }
    }

    .wrapper {
        max-width: 1920px;
        margin: 0 auto;

        @media (min-width: 1920px) {
            max-width: 1600px;
        }

        .action-button {
            font-size: 0.875rem;
        }

        .title-container {
            @media (max-width: $breakpoint-md) {
                width: 100%;
            }
        }
    }
}
</style>
