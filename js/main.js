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
                    var searchBlock = $('.parser-core_search_results');
                    searchBlock.empty();
                    if (dataToRender.length != 0) {
                        for (let key in dataToRender) {
                            let htmlToRender = dataToRender[key]['file_info'];
                            for (let i = 0; i < dataToRender[key]['file_strings'].length; i++) {
                                htmlToRender += dataToRender[key]['file_strings'][i];
                            }
                            searchBlock.append(htmlToRender);
                        }
                    } else {
                        searchBlock.append("<p><h3 style='color: red'>There are no any matches!</h3></p>")
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
    });
});