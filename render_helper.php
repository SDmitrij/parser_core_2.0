<?php

/**
 * @param array $renderData
 * @return void
 */
function renderMainArea(array $renderData)
{

    // Anon. function that renders an html data of files
    $filesHtmlGenerator = function (string $key, array & $renderData): string {

        $blockName = str_replace('_', ' ', $key);
        $content = "<div class='parser-core_$key'>
             <h3>$blockName:</h3><ul>";

        foreach ($renderData[$key] as $file)
        {
            $content .= "<li>" . $file->getFilePath() . "</li>";
        }
        $content .= "</ul></div>";

        return $content;
    };

    // File's data to render
    $htmlContent = '';

    if (!empty($renderData))
    {
        $renderKeys = array_keys($renderData);
        foreach ($renderKeys as $renderKey)
        {
            if (!empty($renderData[$renderKey]))
            {
                $htmlContent .= $filesHtmlGenerator($renderKey, $renderData);
            }

        }

    } else {

        $htmlContent .= "<div class='parser-core_empty'><h3>There are no files or something wrong with parser!</h3></div>";
    }

    echo "<script src='js/jquery-3.3.1.min.js'></script>
        <script src='js/main.js'></script>
        <div class='parser-core_main' style='width: 100%;'><br />
        <div class='parser-core_block_left_index_side'style='float:left; width: 50%'>
        <!--Here must be files to render -->
        " . $htmlContent . "
        </div>
        <br />
        <div class='parser-core_block_right_search_side' style='float:right; width: 50%'>
            <h3>Search form:</h3>
        <div class='parser-core_search_block'>
            <p>Input a word:</p>
            <input class='parser-core_search_by_word_input' type='text'>
            <button class='parser-core_input_send_data'>Search!</button>
        </div>
        </div><br /></div>
        <div style=\"clear:both\"> 
        </div>";
}