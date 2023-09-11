<template>
    <BModal
        title="Welke naam wil je behouden?"
        cancel-title="Annuleren"
        ok-title="Samenvoegen"
        @ok="merge"
        @hide="hide"
        visible
    >
        <p>Alle indexen worden gekoppeld aan de afdeling die je kiest.</p>
        <BFormRadioGroup
            v-model="selectedSection"
            :options="sections"
            label="Alle indexen worden gekoppeld aan de afdeling die je kiest."
            text-field="label"
            value-field="uuid"
            stacked
        />
    </BModal>
</template>

<script lang="ts">
import type { Section } from '@dbco/portal-api/section.dto';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'SectionMergeModal',
    props: {
        sections: {
            type: Array as PropType<Section[]>,
            required: true,
        },
    },
    data() {
        return {
            selectedSection: this.sections[0].uuid as Section['uuid'],
        };
    },
    computed: {
        mainSection() {
            return this.sections.find((s) => s.uuid === this.selectedSection);
        },
        mergeSections() {
            return this.sections.filter((s) => s.uuid !== this.selectedSection);
        },
    },
    methods: {
        hide() {
            this.$emit('on-hide');
        },
        merge() {
            this.$emit('on-merge', this.mainSection, this.mergeSections);
            this.hide();
        },
    },
});
</script>

<style lang="scss" scoped>
::v-deep {
    .custom-control + .custom-control {
        margin-top: 0.5rem;
    }
}
</style>
