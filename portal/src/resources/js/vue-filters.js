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
    'zondag',
    'maandag',
    'dinsdag',
    'woensdag',
    'donderdag',
    'vrijdag',
    'zaterdag'
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

// 23 dec. woensdag
Vue.filter('dateFormatLong', function (value) {
    if (value != null && value.length) {
        const date = new Date(value)
        return date.getDate() + ' ' + monthNamesShort[date.getMonth()] + ' ' + dayNames[date.getDay()]
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
    return ''
})

// 1 - Huisgenoten
Vue.filter('categoryFormatFull', function (category) {

    if (category == null) {
        return ''
    }

    const categories = {
        '1': '1 - Huisgenoot',
        '2a': '2A - Nauw contact',
        '2b': '2B - Nauw contact',
        '3': '3 - Overig contact'
    }

    if (categories[category.toLowerCase()]) {
        return categories[category.toLowerCase()];
    }
    return category
})
