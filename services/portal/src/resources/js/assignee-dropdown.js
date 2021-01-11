
//For every word entered by the user, check if the symbol starts with that word
//If it does show the symbol, else hide it
function userFilter(dropdownMenu, word) {
    let items = dropdownMenu.find('a');
    let length = items.length;
    let hidden = 0;
    for (let i = 0; i < length; i++) {
        if (items[i].text.toLowerCase().includes(word.toLowerCase())) {
            $(items[i]).show();
        }
        else {
            $(items[i]).hide();
            hidden++;
        }
    }

    // If all items are hidden, show the empty view
    if (hidden === length) {
        dropdownMenu.find('.empty').show();
    }
    else {
        dropdownMenu.find('.empty').hide();
    }
}

$('.assignee.dropdown').on('show.bs.dropdown', function () {
    var caseUuid = $(this).data('case');
    var el = $(this).find('.dropdown-menu');
    var isLoaded = (el.find('a').length > 0);
    if (!isLoaded) {
        $('#assignee-list > option').each(function () {
            var link = $('<a>', {
                'text': this.text,
                'class': 'dropdown-item',
                'href': '#',
                'data-assign-case': caseUuid,
                'data-assign-to': this.value
            });
            el.append(link);
        });
    }
});

$('.assignee .search-user').on('input', function() {
    userFilter($(this).closest('.dropdown-menu'), $(this).val());
});

function caseAssign(caseUuid, assigneeUuid, onSuccess, onFailure) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: '/assigncase',
        data: {
            'caseId': caseUuid,
            'userId': assigneeUuid
        },
        dataType: 'json',
        success: function (data) {
            onSuccess();
        },
        error: function (data) {
            var reason = 'Onbekende fout (' + data.status + ')';
            var body = JSON.parse(data.responseText);
            if (body.error) {
                reason = body.error;
            }
            onFailure(reason);
        }
    });
}

$('.assignee .dropdown-menu').on('click', 'A', function() {
   var assigneeUuid = $(this).data('assign-to');
   var assigneeName = $(this).text();
   var caseUuid = $(this).data('assign-case');
   console.log('Assigning '+ caseUuid + ' to ' + assigneeName + ' / ' + assigneeUuid);

   var label = $(this).closest('.assignee').find('.label');

   label.fadeOut(400, function() {
       caseAssign(caseUuid, assigneeUuid,function() {
           label.text(assigneeName).fadeIn(300);
       }, function(failureReason) {
           label.fadeIn(300);
           alert('Toewijzen niet gelukt: ' + failureReason);
       });
   });
});
