<template>
    <div class="w100" @change="onChange">
        <FormulateInput
            v-model="localModel"
            :name="context.name"
            :single="context.single"
            repeatable
            type="group"
            @repeatableRemoved="onChange"
            data-testid="input-group"
        >
            <template #addmore="{ addMore }">
                <div class="d-inline-flex justify-content-start">
                    <BButton
                        block
                        variant="primary"
                        @click="addMore"
                        :disabled="disabled"
                        data-testid="add-more-button"
                    >
                        + Datum toevoegen
                    </BButton>
                </div>
            </template>
            <template #default="{ index }">
                <div class="row">
                    <FormulateInput
                        @change="onChange"
                        class="col m-0"
                        name="day"
                        type="formDatePicker"
                        :calendarView="context.attributes.calendarView"
                        :disabled="disabled"
                        data-testid="form-date-picker"
                        :editable="!disabled"
                        :rangeCutOff="context.attributes.rangeCutOff"
                        singleSelection
                    />
                    <FormulateInput
                        @change="(event) => onTimeStartChange(index, event)"
                        :disabled="disabled"
                        data-testid="input-start-time"
                        class="col m-0 w-40"
                        name="startTime"
                        type="text"
                        placeholder="Vanaf tijd (09:00)"
                    />
                    <FormulateInput
                        @change="(event) => onTimeEndChange(index, event)"
                        :disabled="disabled"
                        data-testid="input-end-time"
                        class="col m-0 w-40"
                        name="endTime"
                        type="text"
                        placeholder="Tot tijd (18:00)"
                    />
                </div>
            </template>
            <template #remove="{ index, removeItem }">
                <BButton
                    :class="context.classes.groupRepeatableRemove"
                    :data-disabled="context.model.length <= context.minimum"
                    data-testid="input-repeatable-remove-button"
                    role="button"
                    :disabled="disabled"
                    @click.prevent="removeItem"
                    v-text="context.removeLabel"
                />
            </template>
        </FormulateInput>
    </div>
</template>
<script>
import { formatTime } from '@/utils/date';

export default {
    name: 'FormDateTimeInputRepeated',
    props: {
        context: {
            type: Object,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            // Map items to prevent object pointer to store
            localModel: (this.context.model || []).map((item) => ({ ...item })),
        };
    },
    methods: {
        onChange() {
            // Update changes to VueFormulate
            this.context.model = this.localModel
                // Filter empty items
                .filter((item) => Object.keys(item).length > 0)
                // Ensure all properties are set
                .map((item) => ({
                    day: item.day || null,
                    startTime: item.startTime || null,
                    endTime: item.endTime || null,
                }));

            this.$emit('change');
        },
        onTimeStartChange(index, $e) {
            this.localModel[index].startTime = formatTime($e.target.value);
        },
        onTimeEndChange(index, $e) {
            this.localModel[index].endTime = formatTime($e.target.value);
        },
    },
};
</script>
