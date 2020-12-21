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

function linkCaseToExport(caseId, remoteId) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: '/linkcasetoexport',
        data: {
            'caseId': caseId,
            'exportId': remoteId
        },
        dataType: 'json',
        success: function (data) {
            $('#upload_' + caseId).attr('disabled', true);
            $('#remote_' + caseId).attr('disabled', true);
        },
        error: function (data) {
            $('#upload_' + caseId).prop('checked', false);
        }
    });
}

function markAsCopied(caseId, taskId, fieldName) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: '/markascopied',
        data: {
            'caseId': caseId,
            'taskId': taskId,
            'fieldName': fieldName
        },
        dataType: 'json'
    });
}

$(".chk-upload-completed").click(function() {
    if ($(this).is(':checked') && $(this).attr('id').indexOf('upload_') !== -1) {
        taskId = $(this).attr('id').substr(7);
        exportId = $('#remote_' + taskId).val() ?? '';
        linkTaskToExport(taskId, exportId);
    }
});

$(".chk-case-upload-completed").click(function() {
    if ($(this).is(':checked') && $(this).attr('id').indexOf('upload_') !== -1) {
        caseId = $(this).attr('id').substr(7);
        console.log('caaaase '+caseId);
        exportId = $('#remote_' + caseId).val() ?? '';
        linkCaseToExport(caseId, exportId);
    }
});

function copyToClipboard(value)
{
    $('#clipboard').val(value).select();
    document.execCommand('Copy');
}

$(".copy-card-values").click(function() {
    var value = $(this).data('copyvalue');
    copyToClipboard(value);
    var btn = $(this);
    var org = btn.html();
    btn.html('&check; Gekopieerd');
    setTimeout(function() {
        btn.html(org);
    }, 3000);
});

$(".copyable.row").click(function() {
    var value = $(this).data('copyvalue');
    copyToClipboard(value);
    var copyField = $(this).data('copyfield');
    if (copyField) {
        console.log('marking copy in session');
        markAsCopied($(this).data('case'), $(this).data('task'), copyField);
    }
    var statusLabel = $(this).find('.row-status').first();
    statusLabel.html('Gekopieerd');
    setTimeout(function() {
        statusLabel.html('&check;');
    }, 2000);
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
