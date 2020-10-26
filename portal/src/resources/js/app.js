require('./bootstrap');

jQuery(document).ready(function($) {
    //  Make clickable rows in tables actually clickable
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });

    function cloneRow(el)
    {
        if (!el.val()) {
            var tr = el.closest('tr');
            var clone = tr.clone(true);
            clone.insertAfter(tr).find('.auto-row-clone').one("keypress", function () {
                cloneRow($(this));
            });

            clone.find('.form-control').each(function (i, input) {
                // Bump input field names
                input.name = input.name.replace(/\d+/, function (n) {
                    return ++n
                });
                input.value = '';
            });
        }
    }

    // Make auto row clone fields actually clone a row (upon the first keypress in the input field)
    $(".auto-row-clone").one("keypress", function() {
        cloneRow($(this));
    });

    // Bind the pairingcode generator
    $('.pairingcode').on('click', function(e) {
        var btn = $(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var type = 'POST';
        var ajaxurl = '/pairing/' + jQuery('#caseUuid').val();
        $.ajax({
            type: type,
            url: ajaxurl,
            data: null,
            dataType: 'json',
            success: function (data) {
                console.log('Pairing..');
                btn.html('<strong>'+data.pairingCode+'</strong>');
                btn.prop('disabled', true);

            },
            error: function (data) {
                console.log(data);
            }
        });
    });
});

