////////////////////////////////////////////////////
//  This file contains all the logic for the
// calendars.
////////////////////////////////////////////////////

try {
    /**
     * By default the LitePicker will include it's styles in the head of the page.
     * We will disable this behaviour because we load it via the css files.
     * @type {boolean}
     */
    window.disableLitepickerStyles = true;

    require('litepicker');
} catch (e) {
}

/**
 * Factory to build datepicker.
 *
 * @param id
 * @param inline (Do not forgot to hide the field your self. e.g. by setting the field to hidden)
 */
const datePickerFactory = (id, inline = false) => {
    const element = document.getElementById(id);

    if (element === null) {
        return null;
    }

    return new Litepicker({
        lang: 'nl',
        moveByOneMonth: true,
        minDate: new Date(Date.now() - 12096e5), // current date minus 14 days
        maxDate: Date.now(),
        numberOfMonths: 2,
        numberOfColumns: 2,
        inlineMode: inline,
        element: element,
        onRender: (element) => {
            const selectedElement = $('.is-start-date.is-end-date');
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
    });
}

/**
 * Initialize calendars.
 */
datePickerFactory('dateofsymptomonset', true);
