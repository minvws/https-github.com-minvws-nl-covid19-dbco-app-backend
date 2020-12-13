////////////////////////////////////////////////////
// RPA utilities
////////////////////////////////////////////////////
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

function copyToClipboard(value)
{
    $('#clipboard').val(value).select();
    document.execCommand('Copy');
}

$(".copy-card-values").click(function() {
    alert("copy-card-values");
});

$(".copyable.row").click(function() {
    var value = $(this).data('copyvalue');
    copyToClipboard(value);

    var statusLabel = $(this).find('.row-status').first();
    statusLabel.html('Gekopieerd');
    setTimeout(function() {
        statusLabel.html('&check;');
    }, 1000);
    statusLabel.css('display', 'block');

    var copyAction = $(this).find('.row-action').first();
    copyAction.css('display', 'none');

    var newDataLabel = $(this).parent().find('.new-data');
    if (newDataLabel.closest('.copyable.row').is($(this))) {
        // fadeOut doesn't work properly if it's under our hover layer, so we hide directly.
        newDataLabel.hide();
    } else {
        newDataLabel.fadeOut();
    }
});

$(".copyable.row").hover(function() {
    var statusLabel = $(this).find('.row-status').first();
    statusLabel.css('display', 'none');
    var copyAction = $(this).find('.row-action').first();
    copyAction.css('display', 'block');
}, function() {
    var copyAction = $(this).find('.row-action').first();
    copyAction.css('display', 'none');
    var statusLabel = $(this).find('.row-status').first();
    statusLabel.css('display', 'block');
});
