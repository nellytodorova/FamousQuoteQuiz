function clickRadioButtonAsSubmit() {
    $('.authorsButtons').click(function() {
        $(this).find('input').attr('checked', true);
        $('.submitButton').click();
    });
}

function processSettings() {
    $(".changeModeButton, input[type=radio], .submitButton").css('display', 'none');
    $("#settingsForm input[type=radio]").parent().addClass('radioMode');

    $("#settingsForm .radioMode").last().addClass('purpleButton');
    $("#quizForm input[type=radio]").parent().addClass('authorsButtons');
}

function changeQuizMode() {
    $('.radioMode').click(function() {
        $('#result').html('');
        $("#result").removeClass('error');
        $("#result").removeClass('success');

        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                ajaxSubmit: '1',
                ajaxSubmitValue: $(this).find('input').val()
            },
            success: function(data) {
                if (data) {
                    $('#result').html(data.message);
                    $("#result").addClass(data.class);
                }
            }
        });
    });
}

$(document).ready(function() {
    processSettings();
    clickRadioButtonAsSubmit();
    changeQuizMode();
});