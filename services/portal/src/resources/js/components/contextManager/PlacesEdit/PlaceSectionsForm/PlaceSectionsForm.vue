<template>
    <BContainer>
        <BRow align-v="center">
            <BCol class="p-0 form">
                <h4>Locatie</h4>
                <div class="place-wrapper">
                    <Place :value="place" />
                    <button type="button" class="form-editable-input-btn" aira-label="edit" @click="$emit('edit')">
                        <img :src="iconEditSvg" aria-hidden="true" alt="" class="mr-4" />
                    </button>
                </div>
            </BCol>
        </BRow>

        <BRow>
            <BCol class="p-0 form">
                <SectionManager />
            </BCol>
        </BRow>

        <BRow>
            <BCol class="p-0">
                <BButton variant="primary" class="w-100 mt-3" @click="savePlace">Opslaan</BButton>
            </BCol>
        </BRow>
    </BContainer>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import Place from '@/components/forms/Place/Place.vue';
import SectionManager from '../SectionManagement/SectionManager/SectionManager.vue';
import iconEditSvg from '@images/icon-edit.svg';

export default defineComponent({
    name: 'PlaceSectionsForm',
    components: {
        Place,
        SectionManager,
    },
    data() {
        return { iconEditSvg };
    },
    props: {
        place: {
            type: Object as PropType<Partial<PlaceDTO>>,
            required: true,
        },
    },
    created() {
        if (!this.place?.uuid?.length) return;
        void this.$store.dispatch('place/FETCH_SECTIONS', this.place.uuid);
        this.$store.commit('place/SET_PLACE', this.place);
    },
    methods: {
        async savePlace() {
            await this.$store.dispatch('place/SAVE_SECTIONS');
            this.$emit('saved');
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.place-wrapper {
    align-items: center;
    border: 1px solid $lightest-grey;
    border-radius: $border-radius-small;
    display: flex;
    margin-bottom: 1.5rem;
    width: 100%;

    button.form-editable-input-btn {
        background: none;
        border: none;
        width: auto;
        height: 30px;
    }
}
</style>
