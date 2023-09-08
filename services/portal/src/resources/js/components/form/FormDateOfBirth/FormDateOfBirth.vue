<template>
    <div>
        <FormulateInput
            v-model="value"
            @blur="format"
            type="text"
            :id="context.id"
            placeholder="DD-MM-JJJJ"
            v-mask="'##-##-####'"
            :disabled="disabled"
            ignored
        />
        <div v-if="context.attributes.displayAge && age && age < 200" data-testid="age">{{ age }} jaar</div>
    </div>
</template>

<script lang="ts">
import { isValid, parse } from 'date-fns';
import { calculateAge, parseDate, formatDate } from '@/utils/date';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { VueFormulateContext } from '../ts/formTypes';
import { nl } from 'date-fns/locale';

export default defineComponent({
    name: 'FormDateOfBirth',
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
        context: {
            type: Object as PropType<VueFormulateContext>,
            required: true,
        },
    },
    watch: {
        value(newVal) {
            let format = 'dd-MM-yyyy';
            if (newVal && newVal.length <= 8) {
                format = 'dd-MM-yy';
            }

            const date = parse(newVal, format, new Date(), {
                locale: nl,
            });

            // Prevent using invalid date or formatting input that is less then 6 characters
            if (!newVal || newVal.length < 6 || !isValid(date)) {
                this.context.model = null;
                return;
            }

            this.context.model = formatDate(date, 'yyyy-MM-dd');
        },
    },
    data() {
        return {
            // Parse the value to our date format, if possible. Else, fallback to model value
            value:
                this.context.model && isValid(parseDate(this.context.model, 'yyyy-MM-dd'))
                    ? formatDate(parseDate(this.context.model, 'yyyy-MM-dd'), 'dd-MM-yyyy')
                    : (this.context.model as Date | null),
        };
    },
    computed: {
        age() {
            if (!this.context.model) return;
            return calculateAge(parseDate(this.context.model, 'yyyy-MM-dd'));
        },
    },
    methods: {
        format() {
            // Force the formatted date on the value
            if (!this.context.model) {
                this.value = this.context.model;
                return;
            }

            const date = parseDate(this.context.model, 'yyyy-MM-dd');
            this.value = isValid(date) ? formatDate(date, 'dd-MM-yyyy') : this.context.model;
        },
    },
});
</script>
