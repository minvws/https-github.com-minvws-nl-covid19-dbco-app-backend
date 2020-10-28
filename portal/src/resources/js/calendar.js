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
        maxDate: Date.now(),
        numberOfMonths: 2,
        numberOfColumns: 2,
        inlineMode: inline,
        element: element,
        onRender: (element) => {
            const el = $('.is-start-date.is-end-date');
            if (el) {
                let stamp = el.attr('data-time');
                let stepSize = 1000 * 60 * 60 * 24;
                let startStamp = stamp - 2 * stepSize;

                let endEl = $('.is-today');
                let endStamp = null;
                if (endEl) {
                    endEl.addClass('is-end-range is-in-range');
                    endStamp = endEl.attr('data-time');
                }
                let dayItems = $(element).find('.day-item');
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

            }
        }
    });
}

/**
 * Initialize calendars.
 */
datePickerFactory('dateofsymptomonset', true);
