<template>
    <FullScreenModal v-if="loaded" v-model="isOpen" :path="[title, label]">
        <template v-slot:header>
            <span v-if="meta">
                {{ meta.caseId }} /
                <strong>{{ meta.name }}</strong>
            </span>
        </template>
        <template v-slot:action>
            <div class="context-editing-modal-action">
                <LastUpdated />
                <BButton variant="primary" @click="isOpen = false" class="tw-ml-2">Terug naar index</BButton>
            </div>
        </template>
        <template v-slot:sidebar>
            <CovidCaseSidebar :schema="schemaSidebar()" />
        </template>
        <FormRenderer :rules="rules()" :schema="schema()" storeType="context" />
    </FullScreenModal>
</template>

<script>
import CovidCaseSidebar from '@/components/caseEditor/CovidCaseSidebar/CovidCaseSidebar.vue';
import LastUpdated from '@/components/caseEditor/LastUpdated/LastUpdated.vue';
import FormRenderer from '@/components/form/FormRenderer/FormRenderer.vue';
import FullScreenModal from '../FullScreenModal/FullScreenModal.vue';
import { getRootSchema, getSchema } from '@/components/form/ts/formSchema';
import { parseDate, isBetweenDays } from '@/utils/date';
import { startOfDay } from 'date-fns';
import { mapState as mapPiniaState } from 'pinia';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { FixedCalendarPeriodV1 } from '@dbco/enum';

export default {
    name: 'ContextEditingModal',
    components: { FullScreenModal, CovidCaseSidebar, LastUpdated, FormRenderer },
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            isOpen: Boolean(this.context),
            loaded: false,
        };
    },
    async created() {
        // Set UUID to store based on passed context
        await this.persistInStore('uuid', this.context.uuid);
        // Set place to store based on passed context, since it can't be retrieved from other API endpoints
        await this.persistInStore('place', this.context.place);

        await this.$store.dispatch('context/LOAD', this.context.uuid);
        this.loaded = true;
    },
    methods: {
        schema: () => getSchema('context-modal'),
        schemaSidebar: () => getSchema('context-modal-sidebar'),
        rules: () => getRootSchema().rules.context,

        openModal() {
            this.isOpen = true;
        },
        async persistInStore(path, values) {
            await this.$store.dispatch('context/CHANGE', { path, values });
        },
        someDateIsInRange(dates, { startDate, endDate }) {
            return dates.some((date) => isBetweenDays(date, parseDate(startDate), parseDate(endDate), '[]'));
        },
    },
    computed: {
        ...mapPiniaState(useCalendarStore, ['getCalendarDateItemsByKey']),
        contextStore() {
            return this.$store.getters['context/fragments'];
        },
        meta() {
            return this.$store.getters['index/meta'];
        },
        place() {
            return this.context.place;
        },

        /**
         * the dates present at the context as a date
         */
        datesPresent() {
            const moments = this.contextStore.general.moments;
            return moments
                ? moments.filter((date) => date.day).map((date) => startOfDay(parseDate(new Date(date.day))))
                : [];
        },
        label() {
            if (this.place && this.place.label) {
                return this.place.label;
            }

            return this.contextStore.general.label;
        },
        title() {
            const ranges = this.getCalendarDateItemsByKey([
                FixedCalendarPeriodV1.VALUE_source,
                FixedCalendarPeriodV1.VALUE_contagious,
            ]);
            const isInSourceRange = ranges[FixedCalendarPeriodV1.VALUE_source]
                ? this.someDateIsInRange(this.datesPresent, ranges[FixedCalendarPeriodV1.VALUE_source])
                : false;

            const isInContagiousRange = ranges[FixedCalendarPeriodV1.VALUE_contagious]
                ? this.someDateIsInRange(this.datesPresent, ranges[FixedCalendarPeriodV1.VALUE_contagious])
                : false;

            if (isInSourceRange && isInContagiousRange) return 'Broncontext & context binnen besmettelijke periode';
            if (isInSourceRange) return 'Broncontext';
            if (isInContagiousRange) return 'Context binnen besmettelijke periode';

            return 'Context';
        },
    },
    watch: {
        isOpen: function (newVal) {
            if (!newVal) {
                this.$emit('onClose');
            }
        },
    },
    destroyed() {
        this.$store.dispatch('context/CLEAR');
    },
};
</script>

<style lang="scss">
.context-editing-modal-action {
    display: flex;
    flex-direction: row;
    align-items: center;
}
</style>
