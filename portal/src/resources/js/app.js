require('./bootstrap');


jQuery(document).ready(function ($) {

    ////////////////////////////////////////////////////
    //  Initialize calendars.
    ////////////////////////////////////////////////////
    require('./calendar');

    ////////////////////////////////////////////////////
    //  Make clickable rows in tables actually
    //  clickable
    ////////////////////////////////////////////////////
    $(".clickable-row").click(function () {
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

            // Also, the current TR now gets its 'delete' button unhidden.
            tr.find('.btn-delete').removeClass('invisible');
        }
    }

    // Make auto row clone fields actually clone a row (upon the first keypress in the input field)
    $(".auto-row-clone").one("keypress", function() {
        cloneRow($(this));
    });

    $(".btn-delete").click(function() {
       $(this).closest('tr').remove();
    });

    function markUploadCompleted(taskId, remoteId) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: '/markupload',
            data: {
                'taskId': taskId,
                'remoteId': remoteId
            },
            dataType: 'json',
            success: function (data) {
                console.log("linked " + taskId + " to " + remoteId);
            },
            error: function (data) {
                console.log(data);
            }
        });
    }

    $(".chk-upload-completed").click(function() {
        if ($(this).is(':checked') && $(this).attr('id').indexOf('upload_') !== -1) {
            taskId = $(this).attr('id').substr(7);
            remoteId = $('#remote_' + taskId).val() ?? '';
            markUploadCompleted(taskId, remoteId);
        }
    });
});
