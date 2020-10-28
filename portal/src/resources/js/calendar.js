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
 * Helper functions to get previous and next siblings.
 */

function getPreviousSiblings(element, max) {
    // Get all the previous elements.
    let prevElements = [];
    let prevElement = element.previousElementSibling;
    let prevCount = 1;

    while (prevElement) {
        if (max !== undefined && prevCount > max) {
            break;
        }

        prevElements.push(prevElement);
        prevElement = prevElement.previousElementSibling;
        prevCount++;
    }

    return prevElements.reverse();
}

function getNextSiblings(element, max) {
    let nextElements = [];
    let nextElement = element.nextElementSibling;
    let nextCount = 1;

    while (nextElement) {
        if (max !== undefined && nextCount > max) {
            break;
        }

        nextElements.push(nextElement);
        nextElement = nextElement.nextElementSibling;
        nextCount++;
    }

    return nextElements;
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
        numberOfMonths: 2,
        numberOfColumns: 2,
        inlineMode: inline,
        element: element,
        onRender: (element) => {
            const selectedElement = element.querySelector('.is-start-date.is-end-date');

            if (selectedElement !== null) {
                const previousElements = getPreviousSiblings(selectedElement, 2);
                const nextElements = getNextSiblings(selectedElement, 8);

                const dateElements = [...previousElements, selectedElement, ...nextElements];
                const dateElementsLength = dateElements.length;

                dateElements.forEach((dateElement, index) => {
                    dateElement.classList.add('is-in-range');

                    if (index === 0) {
                        dateElement.classList.add('is-start-range');
                    } else if (index === dateElementsLength - 1) {
                        dateElement.classList.add('is-end-range');
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
