<template>
    <BFormGroup class="mb-2">
        <div class="title">Wil je die context(en) gebruiken?</div>
        <div class="place-wrapper px-2 mt-2" v-for="place in duplicatePlaces" :key="place.uuid">
            <Place :value="place" />
            <BButton variant="primary" class="mr-3" @click="confirm(place)" size="sm">Kies</BButton>
        </div>
        <div class="title mt-3">Of wil je aan dit adres nog een context toevoegen?</div>
        <div class="place-wrapper px-2 mt-2">
            <Place :value="newPlace" />
            <BButton variant="primary" class="mr-3" @click="confirm(newPlace, true)" size="sm">Kies</BButton>
        </div>
    </BFormGroup>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { PlaceDTO, LocationDTO } from '@dbco/portal-api/place.dto';
import Place from '@/components/forms/Place/Place.vue';
import { StoreType } from '@/store/storeType';
import { PlaceActions } from '@/store/place/placeActions/placeActions';

export default defineComponent({
    name: 'DuplicatePlacesForm',
    components: { Place },
    props: {
        duplicatePlaces: {
            type: Array as PropType<Partial<PlaceDTO>[]>,
            required: true,
        },
        newPlace: {
            type: Object as PropType<Partial<PlaceDTO | LocationDTO>>,
            required: true,
        },
    },
    methods: {
        async confirm(place: Partial<PlaceDTO | LocationDTO>, create = false) {
            if (create) {
                await this.$store.dispatch(`${StoreType.PLACE}/${PlaceActions.CREATE}`, place);
                this.$emit('selectPlace', this.newPlace);
                return;
            }

            this.$emit('selectPlace', place);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.title {
    font-weight: 500;
}

.place-wrapper {
    align-items: center;
    border: 1px solid $lightest-grey;
    border-radius: $border-radius-small;
    display: flex;
    width: 100%;
}
</style>
