<?php
require_once __DIR__ . '\autoloader.php';

spl_autoload_register('autoLoader');

/**
 * @param array $renderData
 * @return void
 */
function renderMainArea(array $renderData)
{
    // Anon. function that renders an html data of files
    $filesHtmlGenerator = function (string $key, array $renderData): string {

        $blockName = str_replace('_', ' ', $key);
        $content = "<div class='parser-core_$key'><h3>$blockName:</h3><ul>";

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

    $mainTemplate = file_get_contents(__DIR__ . '/main_template.html');
    $renderArea = str_replace('<!--Render-->', $htmlContent, $mainTemplate);

    echo $renderArea;
}

/**
 * @param array $filesData
 * @param string $wrdToSrc
 */
function renderSearchResults(array $filesData, string $wrdToSrc): void
{
    $renderData = [];
    $numOfWrdOccur = 0;

    foreach ($filesData['file_strings'] as $fileString)
    {
        $renderData['file_strings'][] = str_ireplace($wrdToSrc, "<text style='color:red'>$wrdToSrc</text>", $fileString) . "<br/>";
        $numOfWrdOccur += substr_count(strtoupper($fileString), strtoupper($wrdToSrc));
    }

    $renderData['file_info'] = "<p><h3 style='color:green'>There are " . $numOfWrdOccur  .
        " matches in: ". $filesData['file_path'] ."</h3></p>";

    // Echo json response
    echo json_encode($renderData);
}

// Handle ajax request and invoke search action
if (isset($_POST['wordToSearch']))
{
    $wrdToSrc = $_POST['wordToSearch'];
    $dir = __DIR__ . '\texts';
    $indexing = new controller\IndexController();
    $paths = $indexing->readFolder($dir);
    $filesController = new controller\FileController();
    $indexing->setFilesRepo(new core\RepoCore('localhost', 'root'));
    $filesController->initFilesObjects($paths);

    if (!empty($filesController->files))
    {
        $filesDataToRender = $indexing->searchAction($wrdToSrc, $filesController->files);
        if (!empty($filesDataToRender))
        {
            renderSearchResults($filesDataToRender, $wrdToSrc);
        }
    }
}
