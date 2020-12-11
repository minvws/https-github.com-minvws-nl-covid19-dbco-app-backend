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

$(".copy-card-values").click(function() {
    alert("copy-card-values");
});

$(".copy-row-value").click(function() {
    alert("copy-row-value");
});
