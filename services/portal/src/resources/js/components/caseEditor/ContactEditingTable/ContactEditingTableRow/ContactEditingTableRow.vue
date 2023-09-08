<template>
    <BTr @change="$emit('change', task)" @mousedown="$emit('click', task.uuid, $event.target)">
        <BTd v-if="task.accessible">
            <div class="flex-start">
                <span class="delete-container">
                    <BButton
                        data-testid="remove-button"
                        v-if="task.uuid"
                        :disabled="!hasTaskUserDeletePermission || !userCanEdit"
                        variant="link"
                        class="border-0"
                        @click="$emit('delete', task.uuid)"
                        ><i class="icon icon--delete m-0 p-0"></i
                    ></BButton>
                </span>
                <BInputGroup>
                    <!-- If the contact data is obfuscated -->
                    <span v-if="task.derivedLabel">
                        <BFormInput :value="task.derivedLabel" readonly class="bg-white" />
                    </span>
                    <span v-else>
                        <BFormInput
                            v-model="task.label"
                            placeholder="Voeg contact toe"
                            ref="contact-input-label"
                            :disabled="!userCanEdit"
                            maxlength="255"
                            @change="$emit('change', task)"
                            :state="isFieldValid('label') ? null : false"
                            validation="required"
                            data-testid="task-label-input"
                        />
                    </span>
                </BInputGroup>
            </div>
        </BTd>
        <BTd v-else class="py-2">
            <span v-if="isContactGroup">Incubatieperiode voorbij - geen besmetting bekend</span>
            <span v-else-if="task.dossierNumber">Zie HPzone dossier: {{ task.dossierNumber }}</span>
            <span v-else>Contact niet gekoppeld aan bronpersoon in HPzone</span>
        </BTd>
        <BTd v-if="isContactGroup">
            <BFormTextarea
                v-if="task.accessible"
                :disabled="!userCanEdit"
                v-model="task.taskContext"
                @blur="resetInputDimensions($event.target)"
                class="expandable-textarea"
                data-testid="task-context-input"
                maxlength="5000"
                placeholder="Bijv. collega of trainer"
                @input="$emit('change', task)"
                @change="$emit('change', task)"
                :state="isFieldValid('taskContext') ? null : false"
            />
        </BTd>
        <BTd v-if="task.accessible">
            <DatePicker
                singleSelection
                :input-class="{ 'is-invalid': !isFieldValid('dateOfLastExposure') }"
                :input-warning="dateWarning(task)"
                calendarClass="right"
                :disabled="!userCanEdit"
                data-testid="date-of-last-exposure"
                :editable="!isMedicalPeriodInfoIncomplete"
                @opened="blurLabelInput"
                @input="$emit('change', task)"
                :ranges="caseDateRanges"
                :range-cut-off="new Date()"
                :default-max="meta.completedAt"
                v-model="task.dateOfLastExposure"
            >
                <template v-slot:alert>
                    <FormInfo
                        v-if="isMedicalPeriodInfoIncomplete"
                        class="info-block--lg mx-1"
                        text="De bron- en/of besmettelijke periode kunnen nog niet worden getoond. Vul minimaal in: klachten, EZD, testdatum."
                        infoType="warning"
                    />
                    <FormInfo
                        v-if="!isMedicalPeriodInfoIncomplete && isMedicalPeriodInfoNotDefinitive"
                        class="info-block--lg mx-1"
                        text="Vul voor definitieve besmettelijke periode minimaal in: klachten, ziekenhuisopname en verminderde afweer."
                        infoType="warning"
                    />
                </template>
            </DatePicker>
        </BTd>
        <BTd v-else>
            {{ $filters.dateFormatLong(task.dateOfLastExposure) }}
        </BTd>
        <BTd v-if="task.accessible">
            <i v-if="task.nature" class="icon--annotated icon--m0 icon"></i>
            <DbcoCategorySelect
                v-model="task.category"
                @input="$emit('change', task)"
                right
                data-testid="task-category"
                :disabled="!userCanEdit"
                :nature.sync="task.nature"
            />
        </BTd>
        <BTd v-else class="pl-4">
            {{ task.category && task.category.toUpperCase() }}
        </BTd>
        <BTd v-if="task.accessible && isContactGroup">
            <BFormRadioGroup
                v-model="task.communication"
                :options="communicationOptions"
                buttons
                :disabled="!userCanEdit"
                button-variant="outline-primary"
                data-testid="task-communication"
                size="sm"
            />
        </BTd>
        <BTd v-if="!task.accessible && isContactGroup" class="text-center">
            {{ task.communication && communicationOptions[task.communication] }}
        </BTd>
        <BTd v-if="!isContactGroup" class="text-center">
            <BFormCheckbox
                :disabled="!task.accessible || !hasTaskEditPermission"
                v-model="task.isSource"
                data-testid="task-is-source"
                :state="isFieldValid('isSource') ? null : false"
            />
        </BTd>
        <BTd class="td-chevron">
            <BSpinner v-if="isSaving" aria-label="Laden" small />
            <BButton
                v-else-if="task.uuid && task.accessible"
                ref="editBtn"
                variant="link"
                class="p-0 border-0"
                @click="$emit('click', task.uuid)"
            >
                <ChevronRight aria-label="Openen" />
            </BButton>
        </BTd>
    </BTr>
</template>

<script lang="ts">
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import DatePicker from '@/components/formControls/DatePicker/DatePicker.vue';
import DbcoCategorySelect from '@/components/formControls/DbcoCategorySelect/DbcoCategorySelect.vue';
import { StoreType } from '@/store/storeType';
import { informedByV1Options, PermissionV1, CalendarViewV1 } from '@dbco/enum';
import {
    getTaskLastContactDateWarning,
    isMedicalPeriodInfoIncomplete,
    isMedicalPeriodInfoNotDefinitive,
} from '@/utils/case';
import { resetInputDimensions } from '@/utils/form';
import { userCanEdit } from '@/utils/interfaceState';
import { mapGetters } from '@/utils/vuex';
import type { PropType } from 'vue';
import type Vue from 'vue';
import { defineComponent } from 'vue';
import ChevronRight from '@icons/chevron-right.svg?vue';
import type { Task } from '@dbco/portal-api/task.dto';
import { TaskGroup } from '@dbco/portal-api/task.dto';
import { useCalendarStore } from '@/store/calendar/calendarStore';

interface Data {
    communicationOptions: typeof informedByV1Options;
}

export default defineComponent({
    name: 'ContactEditingTableRow',
    components: { DatePicker, DbcoCategorySelect, FormInfo, ChevronRight },
    props: {
        errors: {
            type: Array as PropType<string[]>,
            default: () => [],
            required: false,
        },
        isSaving: {
            type: Boolean,
            default: false,
            required: false,
        },
        task: {
            type: Object as PropType<Task>,
            required: true,
        },
    },
    data() {
        return {
            communicationOptions: informedByV1Options,
        } as Data;
    },
    computed: {
        ...mapGetters(StoreType.INDEX, ['meta', 'fragments']),
        caseDateRanges() {
            return useCalendarStore().getCalendarDataByView(
                this.isContactGroup
                    ? CalendarViewV1.VALUE_index_task_contagious_table
                    : CalendarViewV1.VALUE_index_task_source_table
            );
        },
        userCanEdit,
        hasTaskUserDeletePermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_taskUserDelete);
        },
        hasTaskEditPermission() {
            return this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_taskEdit);
        },
        isContactGroup() {
            return this.task.group === TaskGroup.Contact;
        },
        isMedicalPeriodInfoIncomplete() {
            return isMedicalPeriodInfoIncomplete(this.fragments as any);
        },
        isMedicalPeriodInfoNotDefinitive() {
            return isMedicalPeriodInfoNotDefinitive(this.fragments as any);
        },
    },
    methods: {
        blurLabelInput() {
            const inputLabel = this.$refs['contact-input-label'] as Vue;
            const el = inputLabel.$el as HTMLInputElement;

            if (el) {
                el.blur();
            }
        },
        dateWarning(task: Task) {
            return getTaskLastContactDateWarning(task, this.task.group || TaskGroup.Contact, this.fragments as any);
        },
        isFieldValid(fieldName: string) {
            return !this.errors?.includes(`task.${fieldName}`);
        },
        resetInputDimensions,
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
</style>
