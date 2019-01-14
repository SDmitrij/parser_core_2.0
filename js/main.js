$(function () {
    $('.parser-core_input_send_data').click(function () {
        let sendData = $('.parser-core_search_by_word_input').val();
        if (sendData != null && sendData !== '') {
            $.ajax({
                url: 'request_resolver.php',
                type: 'post',
                dataType: 'json',
                data: {wordToSearch: sendData},
                success: function (dataToRender) {
                    console.log(dataToRender);
                }
            })
        }
    })
});