$(function () {
    $('.parser-core_input_send_data').click(function () {
        let sendData = $('.parser-core_search_by_word_input').val();
        if (sendData != null && sendData !== '') {
            $.ajax({
                url: 'resolver.php',
                type: 'post',
                dataType: 'json',
                data: {wordToSearch: sendData},
                success: function (dataToRender) {
                    $('.parser-core_search_results').remove();
                    for (let key in dataToRender) {
                        let htmlToRender = "<div class='parser-core_search_results'>";
                        htmlToRender += dataToRender[key]['file_info'];
                        for (let i = 0; i < dataToRender[key]['file_strings'].length; i++) {
                            htmlToRender += dataToRender[key]['file_strings'][i];
                        }
                        htmlToRender += "</div>";
                        $('.parser-core_search_block').append(htmlToRender);
                    }
                }
            })
        }
    });
    $('.parser-core_indexing').click(function () {
        $.ajax({
            url: 'resolver.php',
            type: 'post',
            data: {index: true},
            success: function (indexStatus) {
                $('.parser-core_block_left_index_side').empty().append(indexStatus);
            }
        })
    })
});