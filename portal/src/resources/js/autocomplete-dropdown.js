
//For every word entered by the user, check if the symbol starts with that word
//If it does show the symbol, else hide it
function userFilter(dropdownList, word) {
    let items = dropdownList.find('a');
    let length = items.length
    let hidden = 0
    for (let i = 0; i < length; i++) {
        if (items[i].text.toLowerCase().includes(word.toLowerCase())) {
            $(items[i]).show()
        }
        else {
            $(items[i]).hide()
            hidden++
        }
    }

    //If all items are hidden, show the empty view
  /*  if (hidden === length) {
        $('#empty').show()
    }
    else {
        $('#empty').hide()
    }*/
}

//If the user clicks on any item, set the title of the button as the text of the item
$('#menuItems').on('click', '.dropdown-item', function(){
    $('#dropdown_user').text($(this)[0].value)
    $("#dropdown_user").dropdown('toggle');
})

// buildDropDown(names)

$('.assignee.dropdown').on('show.bs.dropdown', function () {
    // var caseUuid = $(this).data('case');
    var el = $(this).find('.dropdown-menu');
    var isLoaded = (el.find('a').length > 0);
    if (!isLoaded) {
        $('#assignee-list > option').each(function () {
            var link = $('<a>', {
                'text': this.text,
                'class': 'dropdown-item',
                'href': '#' + this.value
            });
            el.append(link);
        });
    }
});

$('.search-user').on('input', function() {
    userFilter($(this).closest('.dropdown-menu'), $(this).val());
});
