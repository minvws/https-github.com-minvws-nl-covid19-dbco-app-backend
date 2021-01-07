<template>
    <div :id="id"></div>
</template>

<script>
import Litepicker from 'litepicker';

export default {
    name: "DbcoDatepicker",
    props: {
        id: String,
        value: String
    },
    data() {
        return {
            pickerInstance: null,
            mounted: false,
            pickerOptions: {
                lang: 'nl',
                moveByOneMonth: true,
                // TODO/FIXME: This is relative to today, but what if we open an older case? should be relative to case creation date
                minDate: new Date(Date.now() - 28 * 24 * 3600 * 1000),
                maxDate: Date.now(),
                numberOfMonths: 2,
                autoRefresh: true,
                numberOfColumns: 2,
                inlineMode: true,
                element: null, // filled in later
                onSelect: (value) => {
                    if (this.mounted) {
                        const month = value.getMonth() + 1;
                        const day = value.getDate()
                        const dateStr = value.getFullYear() + '-'
                            + (month < 10 ? '0' : '') + month + '-'
                            + (day < 10 ? '0' : '') + day
                            + 'T00:00:00.000000Z'
                        this.$emit('input', dateStr) // this sets the value on the model after selection
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
                        let startStamp = stamp - 2 * stepSize;

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
                    const date = new Date(value);
                    this.pickerOptions.minDate = Math.min(date, this.pickerOptions.minDate)
                    this.pickerInstance.setOptions(this.pickerOptions)
                    this.pickerInstance.setDate(date)
                    this.pickerInstance.gotoDate(date)
                    if (oldValue != '') {
                        // This is slightly iffy. Upon initial load of the page and
                        // setting the value to its stored date, oldValue is an empty string.
                        // In that case, we don't want to emit a select yet, because it wasn't
                        // A user initiated one.
                        // If however oldValue is anything but '' (even null), then the user has
                        // really manually selected something.
                        // This quirk is necessary because the onSelect doesn't distinguish between
                        // user-set new values or model-set initial values.
                        this.$emit('select')
                    }
                }
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
        this.pickerOptions.element = document.getElementById(this.id)
        this.pickerInstance = this.datePickerFactory(this.id, this.pickerOptions, true)
        this.mounted = true
    },
    methods: {
        datePickerFactory(id, pickerOptions, inline = false) {
            const element = document.getElementById(id);

            if (element === null) {
                return null;
            }

            let minDate = new Date();
            minDate.setDate(minDate.getDate() - 28);
            const instance = new Litepicker(pickerOptions);

            const date = new Date();
            instance.gotoDate(date.setMonth(date.getMonth() - 1));
            return instance;
        }
    }
}
</script>

<style scoped>

</style>
