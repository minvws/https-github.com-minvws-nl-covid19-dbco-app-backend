<template>
    <div>
        <BModal
            :title="modalTitle[currentStep]"
            :ok-title="modalOkTitle[currentStep]"
            :ok-disabled="modalOkDisabled"
            visible
            ref="modal"
            @ok="onConfirm"
            @hide="onHide"
        >
            <div v-if="currentStep === Step.SELECT">
                <div v-if="lockedTarget" class="place-wrapper">
                    <Place :value="lockedTarget" />
                </div>
                <div v-if="lockedTarget && notLockedSelections.length > 0" class="font-weight-bold mt-4 mb-1">
                    Samenvoegen met andere context
                </div>
                <div v-if="notLockedSelections.length > 0" class="place-wrapper">
                    <div
                        v-for="place in notLockedSelections"
                        :key="place.uuid"
                        class="d-flex flex-row align-items-center w-100"
                    >
                        <button type="button" @click="$as.any($refs.placeSearch).toggle(place)" class="btn--delete">
                            <i
                                v-if="selected.length > 1"
                                class="icon icon--delete ml-3 mr-0"
                                aria-hidden="true"
                                aria-label="delete"
                            />
                        </button>
                        <Place :value="place" />
                    </div>
                </div>
                <PlaceSearch
                    class="mt-4"
                    :initially-selected="selected"
                    :hideUuid="lockedTargetUuid"
                    ref="placeSearch"
                    @updateSelected="(value) => (selected = value)"
                />
            </div>
            <div v-if="currentStep === Step.PICK_TARGET">
                <p>Alle indexen worden gekoppeld aan de context die je wilt behouden.</p>
                <div class="place-wrapper">
                    <BFormRadioGroup v-model="target">
                        <div
                            v-for="place in selected"
                            :key="place.uuid"
                            class="d-flex flex-row align-items-center w-100"
                        >
                            <BFormRadio :value="place" :id="`place-${place.uuid}`" class="ml-3 mr-0"></BFormRadio>
                            <label :for="`place-${place.uuid}`">
                                <Place :value="place" class="place-radio" />
                            </label>
                        </div>
                    </BFormRadioGroup>
                </div>
            </div>
            <div v-if="currentStep === Step.CONFIRMED">
                <FormInfo class="info-block--lg">
                    <div>
                        <ul class="pl-3">
                            <li v-for="place in selected" :key="place.uuid">
                                {{ place.label }}
                            </li>
                        </ul>
                        <span class="font-weight-bold m-0">Samengevoegd als</span>
                        <p v-if="target && target.address">
                            {{ target.label }}<br />
                            {{ target.categoryLabel }}<br />
                            {{ target.address.street }} {{ target.address.houseNumber }}
                            {{ target.address.houseNumberSuffix }}<br />
                            {{ target.address.postalCode }} {{ target.address.town }}
                        </p>
                    </div>
                </FormInfo>
            </div>
        </BModal>
    </div>
</template>

<script lang="ts">
import type { BModal, BvModalEvent } from 'bootstrap-vue';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { placeApi } from '@dbco/portal-api';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import PlaceComponent from '@/components/forms/Place/Place.vue';
import PlaceSearch from '../PlaceSearch/PlaceSearch.vue';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';

enum Step {
    SELECT = 'Select',
    PICK_TARGET = 'PickTarget',
    CONFIRMED = 'Confirmed',
}

interface Data {
    currentStep: Step;
    isLoading: boolean;
    modalTitle: { [key in Step]: string };
    modalOkTitle: { [key in Step]: string };
    selected: PlaceDTO[];
    Step: typeof Step;
    target?: PlaceDTO;
}

export default defineComponent({
    name: 'PlacesMergeModal',
    components: { Place: PlaceComponent, PlaceSearch, FormInfo },
    props: {
        places: {
            type: Array as PropType<PlaceDTO[]>,
            required: false,
        },
        lockedTargetUuid: {
            type: String,
            required: false,
        },
    },
    data() {
        return {
            currentStep: Step.SELECT,
            isLoading: false,
            modalTitle: {
                [Step.SELECT]: 'Context samenvoegen',
                [Step.PICK_TARGET]:
                    'Welke naam en adresgegevens wil je behouden? Let op: samenvoegen kan niet ongedaan worden gemaakt',
                [Step.CONFIRMED]: 'Contexten samengevoegd',
            },
            modalOkTitle: {
                [Step.SELECT]: 'Samenvoegen',
                [Step.PICK_TARGET]: 'Samenvoegen',
                [Step.CONFIRMED]: 'Sluiten',
            },
            selected: [...(this.places || [])],
            Step,
            target: undefined,
        } as Data;
    },
    computed: {
        lockedTarget() {
            return this.selected.find(({ uuid }) => uuid === this.lockedTargetUuid);
        },
        notLockedSelections() {
            return this.selected.filter(({ uuid }) => uuid !== this.lockedTargetUuid);
        },
        modalOkDisabled() {
            switch (this.currentStep) {
                case Step.SELECT:
                    return this.selected.length < 2;
                case Step.PICK_TARGET:
                    return this.isLoading;
            }

            return false;
        },
    },
    methods: {
        onHide(event: BvModalEvent) {
            if (event.trigger === 'ok') {
                // Only hide the modal when confirming the last step
                event.preventDefault();

                return;
            }

            this.$emit('cancel');
        },
        async onConfirm() {
            switch (this.currentStep) {
                case Step.SELECT:
                    if (this.selected?.length < 2) return;

                    // Select the locked target or fallback to the first place in the selections
                    this.target = this.lockedTarget || this.selected[0];

                    this.currentStep = Step.PICK_TARGET;
                    break;
                case Step.PICK_TARGET:
                    if (!this.target) return;

                    this.isLoading = true;
                    await placeApi.merge(
                        this.target.uuid,
                        this.selected.filter(({ uuid }) => uuid !== this.target?.uuid).map(({ uuid }) => uuid)
                    );
                    this.isLoading = false;

                    this.currentStep = Step.CONFIRMED;

                    this.$emit('success');
                    break;
                case Step.CONFIRMED:
                    // The initial hide was cancelled by the onHide method, we need to wait for that event
                    // to finish before triggering a new hide
                    await this.$nextTick();
                    (this.$refs.modal as BModal).hide();
                    break;
            }
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.place-wrapper {
    align-items: center;
    border: 1px solid $lightest-grey;
    border-radius: $border-radius-small;

    .place-radio {
        cursor: default;
    }
    button.btn--delete {
        background: none;
        border: none;
    }
}
</style>
