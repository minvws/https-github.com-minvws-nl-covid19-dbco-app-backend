<template>
    <div v-if="variant == 'inline'" :id="id"></div>
    <div v-else-if="variant == 'dropdown'">
        <b-dropdown variant="link" text="small" class="dbco-date-select" :id="id">
            <template #button-content>
                {{ value | dateFormatLong }}
                <span v-show="!value">Kies datum</span>
            </template>
        </b-dropdown>
    </div>
</template>

<script>
import Litepicker from 'litepicker';

export default {
    name: "DbcoDatepicker",
    props: {
        id: String,
        value: String,
        /* The symptom date can be passed as a property. If set, the contagious and source
           periods will be rendered accordingly. If set to 'self', the component knows that
           the date you are trying to set is the symptom date, and will move everything accordingly.
           If omitted, the picker acts as a simple date picker without symptom period knowledge.d
         */
        symptomDate: String,
        symptomatic: {
            type: Boolean,
            default: false
        },
        variant: {
            type: String,
            default: 'inline',
            validator: function (value) {
                return ['inline', 'dropdown'].indexOf(value) !== -1
            }

        }
    },
    data() {
        return {
            pickerInstance: null,
            mounted: false,
            shouldEmit: true,
            pickerOptions: {
                lang: 'nl',
                moveByOneMonth: true,
                // TODO/FIXME: This is relative to today, but what if we open an older case? should be relative to case creation date
                minDate: new Date(Date.now() - 28 * 24 * 3600 * 1000),
                maxDate: Date.now(),
                numberOfMonths: 2,
                autoRefresh: true,
                numberOfColumns: 2,
                inlineMode: (this.variant=='inline'),
                element: null, // filled in later
                onSelect: (value) => {
                    if (this.mounted) {
                        const month = value.getMonth() + 1;
                        const day = value.getDate()
                        const dateStr = value.getFullYear() + '-'
                            + (month < 10 ? '0' : '') + month + '-'
                            + (day < 10 ? '0' : '') + day
                            + 'T00:00:00.000000Z'
                        if (this.shouldEmit) {
                            this.$emit('input', dateStr) // this sets the value on the model after selection
                            this.$emit('select') // let the parent know
                        }
                    }
                },
                onRender: (element) => {
                    const selectedElement = $(element).find('.is-start-date.is-end-date');
                    let dayItems = $(element).find('.day-item');

                    /** Remove default selection classes because we will add them later on. */
                    dayItems.each(() => {
                        $(this).removeClass('is-in-range').removeClass('.is-start-range').removeClass('.is-end-range');
                    });

                    /*  Mark contamination period when date is selected. */
                    if (selectedElement.length) {
                        let stamp = selectedElement.attr('data-time');
                        let stepSize = 1000 * 60 * 60 * 24;
                        let startStamp = stamp - this.infectiousnessLeadDays() * stepSize;

                        let endEl = $(element).find('.is-today');
                        let endStamp = null;
                        if (endEl) {
                            endEl.addClass('is-end-range is-in-range');
                            endStamp = endEl.attr('data-time');
                        }
                        let first = true;
                        dayItems.each(function () {
                            let stamp = $(this).attr('data-time');
                            if (stamp >= startStamp && stamp <= endStamp) {
                                $(this).addClass('is-in-range');
                                if (first) {
                                    $(this).addClass('is-start-range');
                                    first = false;
                                }
                            }
                        });
                    } else {
                        $(element).find('.is-today').removeClass('is-end-range').removeClass('is-in-range');
                    }
                }
            }
        }
    },
    watch: {
        value: {
            handler: function(value, oldValue) {
                if (value != null && value !== oldValue) {

                    // Guard changing the picker: it will trigger its own onSelect event, and
                    // we don't wat to get into an event loop. So if WE are triggering the
                    // change, we use the 'selecting' flag to stop a re-emit.
                    this.shouldEmit = false
                    this.setDateOnPicker(value)
                    this.shouldEmit = true
                }

            }
        },
        symptomatic: {
            handler: function(value, oldValue) {
                // Force calendar render if the value changes, since our styles depend on it
                this.pickerInstance.render()
            }
        }
    },
    mounted() {
        /**
         * By default the LitePicker will include it's styles in the head of the page.
         * We will disable this behaviour because we load it via the css files.
         * @type {boolean}
         */
        window.disableLitepickerStyles = true
        // Late binding to the element (not yet available when it was first initialized)
        let id = this.id;
        if (this.variant == 'dropdown') {
            // must now bind to the scoped id
            id = id + '__BV_toggle_';
        }
        this.pickerOptions.element = document.getElementById(id)
        this.pickerInstance = this.datePickerFactory(this.pickerOptions)
        this.setDateOnPicker(this.value)

        this.mounted = true


    },
    methods: {
        datePickerFactory(pickerOptions) {
            const instance = new Litepicker(pickerOptions);
            const date = new Date();
            instance.gotoDate(date.setMonth(date.getMonth() - 1));
            return instance;
        },
        setDateOnPicker(value) {
            const date = new Date(value);
            this.pickerOptions.minDate = Math.min(date, this.pickerOptions.minDate)
            this.pickerInstance.setOptions(this.pickerOptions)
            this.pickerInstance.setDate(date)
            this.pickerInstance.gotoDate(date)
        },
        infectiousnessLeadDays() {
            if (this.symptomatic == true) {
                return 2;
            }
            return 0;
        }
    }
}
</script>

<style scoped>

</style>
