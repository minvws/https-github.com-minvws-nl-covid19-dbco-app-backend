<template>
    <div v-if="isCaseFinished" class="bg-white sticky-top">
        <FormInfo
            ref="infobar"
            class="info-block--lg justify-content-center"
            text="Dit BCO is afgerond. Het dossier in HPZone is mogelijk actueler. Voer eventuele wijzigingen die je hier maakt daarom ook in HPZone door."
            infoType="warning"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContactTracingStatusV1 } from '@dbco/enum';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { mapGetters } from '@/utils/vuex';

export default defineComponent({
    name: 'InfoBar',
    components: { FormInfo },
    mounted() {
        window.addEventListener('scroll', this.emitHeight, true);
        window.addEventListener('resize', this.emitHeight);
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.emitHeight, true);
        window.removeEventListener('resize', this.emitHeight);
    },
    computed: {
        ...mapGetters('index', ['meta']),
        isCaseFinished(): boolean {
            return [
                ContactTracingStatusV1.VALUE_closed_outside_ggd,
                ContactTracingStatusV1.VALUE_closed_no_collaboration,
                ContactTracingStatusV1.VALUE_completed,
            ].includes(this.meta.statusIndexContactTracing!);
        },
    },
    methods: {
        emitHeight() {
            this.$emit('height', (this.$el as HTMLElement).offsetHeight);
        },
    },
});
</script>
