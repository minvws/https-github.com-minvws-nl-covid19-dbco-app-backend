<template>
    <div class="section" data-testid="add-situation-number-button-wrapper">
        <template>
            <BForm class="section-form">
                <fieldset :class="`section-fieldset${situationNumbers && situationNumbers.length ? '--items' : ''}`">
                    <legend class="col-form-label">
                        <label for="situation-number-change">Situatienummer</label>
                        <label
                            for="situation-name-change"
                            class="situation-name-field"
                            v-if="situationNumbers && situationNumbers.length"
                            >Naam situatie</label
                        >
                    </legend>

                    <div
                        v-if="situationNumbers && situationNumbers.length"
                        v-for="(item, index) in situationNumbers"
                        :key="item.uuid + index"
                    >
                        <div class="input-wrapper">
                            <BFormInput
                                label="Situatienummer"
                                id="situation-number-change"
                                data-testid="situation-number-change"
                                debounce="300"
                                autocomplete="off"
                                class="situation-number-change"
                                v-model="item.value"
                            />
                        </div>

                        <div class="input-wrapper">
                            <BFormInput
                                label="Naam situatie"
                                id="situation-name-change"
                                data-testid="situation-name-change"
                                debounce="300"
                                autocomplete="off"
                                class="situation-name-change"
                                v-model="item.name"
                            />
                        </div>
                        <BButton
                            class="w-auto remove-situation-number-button"
                            @click="removeSituation(item)"
                            data-testid="remove-situation-number-button"
                            variant="outline-primary"
                        >
                            <i class="icon icon--m0 icon--trash"></i>
                        </BButton>
                    </div>

                    <BButton
                        class="w-auto add-situation-number-button"
                        @click="addPlaceHolderSituation"
                        data-testid="add-situation-number-button"
                        variant="outline-primary"
                    >
                        <i class="icon ml-0 icon--add"></i>
                        Situatienummer toevoegen
                    </BButton>
                </fieldset>
            </BForm>
        </template>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, ref, unref, watchEffect } from 'vue';
import { v4 as uuidv4 } from 'uuid';
import { usePlaceSituationStore } from '@/store/placeSituation/placeSituationStore';
import type { PlaceSituation } from '@dbco/portal-api/place.dto';

export default defineComponent({
    props: {
        situationsFromApi: {
            type: Array as PropType<Array<PlaceSituation> | undefined>,
            required: false,
        },
    },
    setup(props) {
        const store = usePlaceSituationStore();
        const situationNumbers = ref<Array<PlaceSituation>>(props.situationsFromApi || []);
        const addPlaceHolderSituation = () => {
            situationNumbers.value.push({ uuid: uuidv4(), name: '', value: '' });
        };
        const removeSituation = (item: PlaceSituation) => {
            const s = unref(situationNumbers);
            const indexInArray = s.findIndex((i) => i.uuid === item.uuid);
            s.splice(indexInArray, 1);
        };

        watchEffect(() => {
            store.updateSituations(situationNumbers.value);
        });
        return {
            store,
            situationNumbers,
            addPlaceHolderSituation,
            removeSituation,
        };
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.section {
    border-top: 1px solid $bco-purple-light;
    margin-top: $padding-md;
    padding-top: $padding-md;

    &-fieldset legend {
        display: block;
        margin-bottom: $padding-xs;
        padding-top: 0;
    }

    &-fieldset--items {
        display: grid;
        grid-template-columns: 4fr 3fr auto;
        gap: 1rem 0.5rem;

        > div,
        legend {
            display: contents;
        }
    }

    legend {
        font-weight: 500;

        label {
            margin-bottom: 0;
        }
    }

    .icon--add {
        width: 14px;
        height: 14px;
        margin-right: 4px;
    }

    .situation-name-field {
        grid-column: 2 / span 2;
    }
}

.add-situation-number-button {
    font-size: 0.875rem;
}

.remove-situation-number-button {
    border-color: $bco-purple-light;
}
</style>
