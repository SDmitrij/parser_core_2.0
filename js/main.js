$(function () {
    $('.parser-core_input_send_data').click(function () {
        let sendData = $('.parser-core_search_by_word_input').val();
        if (sendData != null && sendData !== '') {
            $.ajax({
                url: 'helper.php',
                type: 'post',
                dataType: 'json',
                data: {wordToSearch: sendData},
                success: function (dataToRender) {
                    let htmlToRender = "<div class='parser-core_search_results'>";
                    htmlToRender += dataToRender['file_info'];

                    for (let i = 0; i < dataToRender['file_strings'].length; i++)
                    {
                       htmlToRender += dataToRender['file_strings'][i];
                    }

                    htmlToRender += "</div>";

                    $('.parser-core_search_results').remove();

                    $('.parser-core_search_block').append(htmlToRender);
                }
            })
        }
    })
});