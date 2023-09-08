<template>
    <div>
        <BTableSimple class="table-ggd table--spacious table--clickable">
            <colgroup>
                <col class="w-25" />
                <col class="w-10" />
                <col class="w-35" />
                <col class="w-30" />
            </colgroup>
            <BThead>
                <BTr>
                    <BTh scope="col">Omschrijving</BTh>
                    <BTh scope="col">Postcode</BTh>
                    <BTh scope="col">Adres</BTh>
                    <BTh scope="col">Gegevens</BTh>
                </BTr>
            </BThead>
            <BTbody>
                <BTr
                    v-for="(context, index) in contexts"
                    :key="index"
                    :class="{ context: true, linked: !!context.placeUuid, 'not-linked': !context.placeUuid }"
                    @click="selectContext(context)"
                >
                    <BTd>
                        <div class="description-label">
                            <i
                                class="icon icon--m0 icon--connected"
                                v-b-tooltip.hover
                                :title="`${context.label} is gelinkt aan een context`"
                                v-show="context.placeUuid"
                            ></i>
                            <div>
                                <span v-if="context.place && context.place.label">
                                    {{ context.place.label }}
                                </span>
                                <span v-else>
                                    {{ context.label }}
                                </span>
                                <span v-if="context.place && context.place.isVerified" class="verified">
                                    <CheckIcon class="mr-1" />geverifieerd
                                </span>
                            </div>
                        </div>
                    </BTd>
                    <BTd>
                        <span v-if="context.place && context.place.address">{{
                            formatPostalCode(context.place.address.postalCode)
                        }}</span>
                    </BTd>
                    <BTd>
                        <span v-if="context.place && context.place.address">
                            {{ context.place.address.street }}
                            {{ context.place.address.houseNumber }}{{ context.place.address.houseNumberSuffix }},
                            {{ formatPostalCode(context.place.address.postalCode) }}, {{ context.place.address.town }},
                            {{ context.place.address.country }}
                        </span>
                    </BTd>
                    <BTd class="td--data-status">
                        <div class="d-flex justify-content-end align-items-center">
                            <div
                                class="align-self-start"
                                data-testid="missing-dates-alert"
                                role="alert"
                                v-if="context.moments.length === 0"
                            >
                                <img :src="checkErrorSvg" class="mr-2" aria-hidden="true" alt="" />
                                Datum ontbreekt
                            </div>
                            <span class="moments-label" v-else>{{ getLabelForContextMoments(context.moments) }}</span>
                            <i class="icon icon--chevron-right context-chevron ml-2"></i>
                        </div>
                    </BTd>
                </BTr>
            </BTbody>
        </BTableSimple>
        <ContextEditingModal v-if="selected" :context="selected" @onClose="deselectContext" />
    </div>
</template>

<script>
import { contextApi } from '@dbco/portal-api';

import { infectiousDates, sourceDates } from '@/utils/case';
import { parseDate, getDifferenceInDays } from '@/utils/date';
import { PermissionV1 } from '@dbco/enum';
import checkErrorSvg from '@images/check-error.svg';
import CheckIcon from '@icons/check.svg?vue';

import ContextEditingModal from '@/components/modals/ContextEditingModal/ContextEditingModal.vue';

export default {
    name: 'ContextSummaryTable',
    components: { ContextEditingModal, CheckIcon },
    props: {
        caseUuid: {
            type: String,
            required: true,
        },
        group: {
            type: String,
        },
    },
    data() {
        return {
            contexts: [],
            loaded: false,
            selected: null,
            checkErrorSvg,
        };
    },
    mounted() {
        this.loadContexts(this.caseUuid);
    },
    computed: {
        fragments() {
            return this.$store.getters['index/fragments'];
        },
        datesSource() {
            return infectiousDates(this.fragments);
        },
        hasContextEditPermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_contextEdit);
        },
    },
    methods: {
        async loadContexts(caseUuid) {
            const data = await contextApi.getContexts(caseUuid);
            this.contexts = data.contexts;
            this.loaded = true;
        },
        getLabelForContextMoments(moments) {
            let contagious = false;
            let source = false;

            const infectiousDateRange = infectiousDates(this.fragments);
            const sourceDateRange = sourceDates(this.fragments);

            if (infectiousDateRange) {
                const isOnOrBeforeInfeciousStartDate = (moment) => {
                    const date = parseDate(moment, 'yyyy-MM-dd');
                    const diff = getDifferenceInDays(date, infectiousDateRange.startDate);
                    return diff >= 0;
                };
                contagious = moments.some(isOnOrBeforeInfeciousStartDate);
            }
            if (sourceDateRange) {
                const isOnOrAfterSourcesEndDate = (moment) => {
                    const date = parseDate(moment, 'yyyy-MM-dd');
                    const diff = getDifferenceInDays(sourceDateRange.endDate, date);
                    return diff >= 0;
                };
                source = moments.some(isOnOrAfterSourcesEndDate);
            }

            if (contagious && source) return 'Bron & Besmettelijk';
            if (contagious) return 'Besmettelijk';
            if (source) return 'Bron';
            return 'Nog geen datums ingevuld';
        },
        selectContext(context) {
            if (!context.uuid) return;
            this.selected = context;
            this.selectedToLink = null;
        },
        deselectContext() {
            this.selected = null;
            void this.loadContexts(this.caseUuid);
        },
        /**
         * Formats the postal code with a space, to make it easier to copy and paste into HPZone
         */
        formatPostalCode(postalCode) {
            if (!postalCode) {
                return undefined;
            }

            const match = postalCode.match(/^(\d{4}) *(\w{2})$/);
            if (!match) return postalCode;

            return `${match[1]} ${match[2]}`;
        },
    },
};
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.context {
    &.not-linked {
        height: 70px;
    }
    &.linked {
        cursor: pointer;
    }
}

.context-chevron {
    color: #d0d0db;
    margin: 0;
    height: 1.25rem;
    width: 1.25rem;
    padding-left: 0.75rem;
}

.align-self-start {
    margin-right: auto;
}

.moments-label {
    justify-self: flex-start;
    margin-right: auto;
}

.description-label {
    display: flex;
    align-items: center;

    & > * + * {
        margin-left: 0.75rem;
    }
}

.verified {
    display: flex;
    align-items: center;
    color: $bco-info;

    svg {
        height: 0.6875rem;
        color: $bco-info;
    }
}
</style>
