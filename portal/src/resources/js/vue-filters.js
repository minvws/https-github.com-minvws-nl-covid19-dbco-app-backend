Vue.filter('truncate', function (value, limit) {
    if (value != null) {
        if (value.length > limit) {
            value = value.substring(0, (limit - 3)) + '...'
        }
        return value
    }
    return ''
})

const dayNames = [
    'Zondag',
    'Maandag',
    'Dinsdag',
    'Woensdag',
    'Donderdag',
    'Vrijdag',
    'Zaterdag'
]

const monthNamesShort = [
    'jan.',
    'feb.',
    'maa.',
    'apr.',
    'mei',
    'jun.',
    'jul.',
    'aug.',
    'sep.',
    'okt.',
    'nov.',
    'dec.'
]

// woensdag 23 dec.
Vue.filter('dateFormatLong', function (value) {
    if (value != null && value.length) {
        const date = new Date(value)
        return dayNames[date.getDay()] + ' ' + date.getDate() + ' ' + monthNamesShort[date.getMonth()]
    }
    return ''
})

// Gisteren 20:23
Vue.filter('dateFormatDeltaTime', function (value) {
    if (value != null && value.length) {
        const date = new Date(value);
        const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
        const diffDays = Math.round(Math.abs((new Date() - date) / oneDay))
        let dateString = ''
        if (diffDays == 0) {
             dateString = 'Vandaag '
        } else if (diffDays == 1) {
            dateString = 'Gisteren '
        } else if (diffDays > 1 && diffDays < 6) {
            dateString = dayNames[date.getDay()] + ' '
        } else {
            dateString = dayNames[date.getDay()] + ' ' + date.getDate() + ' ' + monthNamesShort[date.getMonth()] + ' '
        }
        return dateString + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes()
    }
    return '';
})
