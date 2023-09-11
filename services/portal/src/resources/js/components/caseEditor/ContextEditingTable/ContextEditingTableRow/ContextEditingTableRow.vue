<template>
    <BTr
        :class="{ disabled: isDatePickerOpen }"
        @mousedown="!isDatePickerOpen && !isSaving && $emit('click', context.uuid, $event)"
    >
        <BTd>
            <div class="flex-start">
                <span class="delete-container">
                    <BButton
                        @click="$emit('delete', context.uuid)"
                        data-testid="delete-button"
                        v-if="context.uuid"
                        :disabled="!hasContextDeletePermission || !userCanEdit"
                        variant="link"
                        class="border-0"
                    >
                        <i class="icon icon--delete m-0 p-0"></i>
                    </BButton>
                </span>
                <BInputGroup>
                    <span v-if="context.place && context.place.label" data-testid="label-text" class="label--linked">
                        {{ context.place.label }}
                    </span>
                    <BFormInput
                        v-else
                        @change="$emit('change', context)"
                        @input="$emit('change', context)"
                        lazy
                        maxlength="255"
                        placeholder="Omschrijving"
                        v-model="context.label"
                        :disabled="!userCanEdit"
                        data-testid="label-input"
                        :state="isFieldValid('label') ? null : false"
                    />
                    <i
                        v-if="context.placeUuid"
                        class="icon icon--m0 icon--connected"
                        data-testid="icon-connected"
                        v-b-tooltip.hover
                        :title="`${context.label} is gelinkt aan een context`"
                    ></i>
                </BInputGroup>
            </div>
        </BTd>
        <BTd>
            <BFormTextarea
                v-model="context.remarks"
                @blur="resetInputDimensions($event.target)"
                @change="$emit('change', context)"
                @input="$emit('change', context)"
                class="expandable-textarea"
                lazy
                maxlength="5000"
                placeholder="Toelichting"
                :disabled="!userCanEdit"
                data-testid="remarks-textarea"
                :state="isFieldValid('remarks') ? null : false"
            />
        </BTd>
        <BTd>
            <DatePicker
                @close="onCloseDatePicker"
                @opened="onOpenDatePicker"
                calendarClass="right top"
                data-testid="moments-datepicker"
                :default-max="meta.completedAt"
                :disabled="!userCanEdit"
                :input-class="{ 'is-invalid': !isFieldValid('moments') }"
                :input-warning="dateWarning"
                :ranges="dateRanges"
                :range-cut-off="new Date()"
                :value="context.moments"
            >
                <template v-slot:alert>
                    <FormInfo
                        v-if="isMedicalPeriodInfoIncomplete($as.any(fragments))"
                        class="info-block--lg mx-1"
                        text="De bron- en/of besmettelijke periode kunnen nog niet worden getoond. Vul minimaal in: klachten, EZD, testdatum."
                        infoType="warning"
                    /><FormInfo
                        v-if="
                            !isMedicalPeriodInfoIncomplete($as.any(fragments)) &&
                            isMedicalPeriodInfoNotDefinitive($as.any(fragments))
                        "
                        class="info-block--lg mx-1"
                        text="Vul voor definitieve besmettelijke periode minimaal in: klachten, ziekenhuisopname en verminderde afweer."
                        infoType="warning"
                    />
                </template>
            </DatePicker>
        </BTd>
        <BTd>
            <BFormSelect
                @change="$emit('change', context)"
                :disabled="!userCanEdit"
                data-testid="relationship-select"
                v-model="context.relationship"
                :options="relationshipOptions"
                :state="isFieldValid('relationship') ? null : false"
            />
        </BTd>
        <BTd v-if="group !== ContextGroup.Contagious">
            <BFormCheckbox
                v-if="context.moments && context.moments.length > 0"
                @change="$emit('change', context)"
                :disabled="!userCanEdit"
                data-testid="is-source-checkbox"
                v-model="context.isSource"
                :state="isFieldValid('isSource') ? null : false"
            />
        </BTd>
        <BTd class="td-chevron">
            <BSpinner aria-label="context-loading-button" v-if="context.uuid && isSaving" small />
            <BButton
                v-else-if="context.uuid"
                @click="$emit('click', context.uuid, $event)"
                data-testid="context-edit-button"
                variant="link"
                class="p-0 border-0"
                :disabled="isDatePickerOpen"
            >
                <ChevronRight />
            </BButton>
        </BTd>
    </BTr>
</template>

<script lang="ts">
import { useModal } from '@/components/AppHooks';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { ContextGroup } from '@/components/form/ts/formTypes';
import DatePicker from '@/components/formControls/DatePicker/DatePicker.vue';
import ChevronRight from '@icons/chevron-right.svg?vue';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { StoreType } from '@/store/storeType';
import { contextRelationshipV1Options, PermissionV1, CalendarViewV1 } from '@dbco/enum';
import {
    infectiousDates,
    isMedicalPeriodInfoIncomplete,
    isMedicalPeriodInfoNotDefinitive,
    sourceDates,
} from '@/utils/case';
import { areAllDatesInOtherContextGroup, classifyDates, getClassificationWarning } from '@/utils/context';
import { parseDate } from '@/utils/date';
import { resetInputDimensions } from '@/utils/form';
import { userCanEdit as userCanEditFn } from '@/utils/interfaceState';
import { useStore } from '@/utils/vuex';
import _ from 'lodash';
import type { PropType } from 'vue';
import { computed, defineComponent, ref } from 'vue';
import type { Context } from '@dbco/portal-api/context.dto';
import { useCalendarStore } from '@/store/calendar/calendarStore';

export default defineComponent({
    name: 'ContextEditingTableRow',
    components: { DatePicker, FormInfo, ChevronRight },
    emits: {
        /* c8 ignore start */
        /* eslint-disable @typescript-eslint/no-unused-vars */
        change: (context: Context) => true,
        click: (uuid: string, $event: Event) => true,
        delete: (uuid: string) => true,
        move: (context: Context) => true,
        /* eslint-enable @typescript-eslint/no-unused-vars */
        /* c8 ignore stop */
    },
    props: {
        context: {
            type: Object as PropType<Context>,
            required: true,
        },
        errors: {
            type: Array as PropType<string[]>,
            default: () => [],
            required: false,
        },
        group: {
            type: String as PropType<ContextGroup>,
            required: true,
            validator: (prop: ContextGroup) => Object.values(ContextGroup).includes(prop),
        },
        isSaving: {
            type: Boolean,
            default: false,
            required: false,
        },
    },
    setup(props, ctx) {
        const isDatePickerOpen = ref(false);

        const modal = useModal();
        const store = useStore();

        const fragments = computed(() => store.getters[`${StoreType.INDEX}/fragments`] as CovidCaseUnionDTO);
        const meta = computed(() => store.getters[`${StoreType.INDEX}/meta`]);
        const dateRanges = computed(() =>
            useCalendarStore().getCalendarDataByView(CalendarViewV1.VALUE_index_context_table)
        );
        const hasContextDeletePermission = computed(() =>
            store.getters[`${StoreType.USERINFO}/hasPermission`](PermissionV1.VALUE_contextDelete)
        );
        const userCanEdit = computed(userCanEditFn);

        const dateWarning = computed(() => {
            let dates: Date[] = [];
            if (props.context.moments) {
                dates = props.context.moments.map((dateString) => parseDate(dateString, 'yyyy-MM-dd'));
            }

            return getClassificationWarning(
                classifyDates(dates, sourceDates(fragments.value), infectiousDates(fragments.value))
            );
        });

        const isFieldValid = (fieldName: string) => !props.errors?.includes(`context.${fieldName}`);

        const onCloseDatePicker = (moments: string[]) => {
            isDatePickerOpen.value = false;

            // exit early if no changes
            if (_.isEqual(moments, props.context.moments)) return;

            const dateClassifications = classifyDates(
                moments.map((dateString) => parseDate(dateString, 'yyyy-MM-dd')),
                sourceDates(fragments.value),
                infectiousDates(fragments.value)
            );
            if (areAllDatesInOtherContextGroup(dateClassifications, props.group)) {
                modal.show({
                    title: `Deze context valt alleen binnen de ${
                        props.group === ContextGroup.Source ? 'besmettelijke periode' : 'bronperiode'
                    }`,
                    text: `Wil je de context naar het ${
                        props.group === ContextGroup.Source ? 'contactonderzoek' : 'brononderzoek'
                    } verplaatsen? Zo niet, dan veranderen de gegevens weer terug naar de oorspronkelijke datum(s)`,
                    okTitle: 'Verplaatsen',
                    onConfirm: () => {
                        ctx.emit('change', {
                            ...props.context,
                            moments,
                        });
                    },
                    onCancel: () => {
                        // To undo the changes we need to trigger a re-render
                        props.context.moments = [...(props.context.moments || [])];
                    },
                });
            } else if (props.context.uuid || moments.length > 0) {
                // Only update when existing row or moments have been selected
                // Prevents a placeholder from posting when not selecting dates
                ctx.emit('change', {
                    ...props.context,
                    moments,
                });
            }
        };

        const onOpenDatePicker = () => {
            isDatePickerOpen.value = true;

            (document.activeElement as HTMLElement)?.blur();
        };

        return {
            ContextGroup,
            fragments,
            meta,

            isDatePickerOpen,
            relationshipOptions: contextRelationshipV1Options,

            hasContextDeletePermission,
            userCanEdit,

            dateRanges,
            isMedicalPeriodInfoIncomplete,
            isMedicalPeriodInfoNotDefinitive,

            dateWarning,
            isFieldValid,
            onCloseDatePicker,
            onOpenDatePicker,
            resetInputDimensions,
        };
    },
});
</script>

<style lang="scss" scoped>
.delete-container {
    position: absolute;
    left: -2.25rem;
    width: 2rem;
    height: 2rem;
}

.expandable-textarea {
    height: 2rem;
    overflow: hidden;
    resize: none;

    &:focus {
        height: 8rem;
        width: 150%;
        overflow: auto;
        position: absolute;
        resize: both;
        z-index: 2;
    }
}

.icon--connected {
    margin: 0.5rem;
}

.label--linked {
    line-height: 2rem;
    flex: 1 1 auto;
    width: 1%;
}
</style>
