<template>
    <div>
        <div class="no-place" v-if="!place">
            <p class="note">Er is nog geen locatie gekoppeld aan deze context.</p>
            <BButton
                @click="modalVisible = true"
                variant="primary"
                :disabled="disabled"
                data-testid="select-place"
                class="w-auto"
                >Context koppelen</BButton
            >
        </div>
        <div class="selected-place" v-else>
            <Place
                :value="place"
                :isCancellable="true"
                :isEditable="true"
                :disabled="disabled"
                data-testid="place"
                @cancel="onCancel"
                @edit="modalVisible = true"
            />
            <BRow v-if="sections.length !== 0" class="mt-4" data-testid="sections">
                <BCol>
                    <p>Afdelingen, teams, klassen, lijn- of vluchtnummers waar index aanwezig is geweest</p>
                </BCol>
                <BCol>
                    <ul>
                        <li v-for="(section, $index) in sections" :key="$index">
                            {{ section.label }}
                        </li>
                    </ul>
                </BCol>
            </BRow>
        </div>
        <PlaceSelectModal v-if="modalVisible" :initialQuery="generalLabel" @hide="closeModal" />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { contextApi } from '@dbco/portal-api';
import Place from '@/components/forms/Place/Place.vue';
import PlaceSelectModal from '@/components/modals/PlaceSelectModal/PlaceSelectModal.vue';
import { StoreType } from '@/store/storeType';
import { mapGetters } from '@/utils/vuex';
import type { Section } from '@dbco/portal-api/section.dto';

export default defineComponent({
    name: 'ContextPlace',
    components: { Place, PlaceSelectModal },
    data() {
        return {
            modalVisible: false,
            sections: [] as Section[],
        };
    },
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    created() {
        void this.loadSections();
    },
    computed: {
        ...mapGetters(StoreType.CONTEXT, {
            contextUuid: 'uuid',
            fragments: 'fragments',
            place: 'place',
        }),
        generalLabel() {
            return this.fragments.general?.label || undefined;
        },
    },
    methods: {
        async closeModal() {
            await Promise.all([this.loadSections(), this.refreshFragments()]);
            this.modalVisible = false;
        },
        async loadSections() {
            const { sections } = await contextApi.getSections(this.contextUuid!);
            this.sections = sections;
        },
        refreshFragments() {
            void this.$store.dispatch('context/LOAD', this.contextUuid);
        },
        async onCancel() {
            // Need to store this place.uuid, because the store dispatch will remove it for the component.
            const placeUuid = (this.place as any).uuid;
            await this.$store.dispatch('context/CHANGE', { path: 'place', values: null });

            // Save to BE
            await contextApi.unlinkPlace(this.contextUuid!, placeUuid);
            void this.refreshFragments();
        },
    },
    watch: {
        place() {
            void this.loadSections();
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.no-place {
    display: flex;
    align-items: center;
    justify-content: space-between;

    p {
        font-weight: 500;
        margin: 0;
    }
}

.selected-place {
    .place {
        border: 1px solid $bco-purple-light;
    }

    ul {
        font-weight: 500;
    }
}
</style>
