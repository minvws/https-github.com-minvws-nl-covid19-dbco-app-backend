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
            clone.insertAfter(tr).find('.auto-row-clone').one("focus", function () {
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
    $(".auto-row-clone").one("focus", function() {
        cloneRow($(this));
    });

    $(".btn-delete").click(function() {
       $(this).closest('tr').remove();
    });

    function linkTaskToExport(taskId, remoteId) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            url: '/linktasktoexport',
            data: {
                'taskId': taskId,
                'exportId': remoteId
            },
            dataType: 'json',
            success: function (data) {
                $('#upload_' + taskId).attr('disabled', true);
                $('#remote_' + taskId).attr('disabled', true);
            },
            error: function (data) {
                $('#upload_' + taskId).prop('checked', false);
            }
        });
    }

    $(".chk-upload-completed").click(function() {
        if ($(this).is(':checked') && $(this).attr('id').indexOf('upload_') !== -1) {
            taskId = $(this).attr('id').substr(7);
            exportId = $('#remote_' + taskId).val() ?? '';
            linkTaskToExport(taskId, exportId);
        }
    });
});
