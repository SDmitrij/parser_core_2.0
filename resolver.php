<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';

spl_autoload_register('autoLoader');

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'texts';

try {
    $filesRepo = new \core\RepoCore('localhost', 'root');
    $indexing = new controller\IndexController();
    $paths = $indexing->readFolderAction($dir);
    $filesController = new controller\FileController();
    $indexing->setFilesRepo($filesRepo);
    $filesController->initFilesObjects($paths);
} catch (\Exception $exception) {
    echo $exception->getMessage();
}

// Handle ajax request and invoke search action
if (isset($_POST['wordToSearch']))
{
    $wrdToSrc = $_POST['wordToSearch'];
    if (!empty($filesController->files))
    {
        $filesDataToRender = $indexing->searchAction($wrdToSrc, $filesController->files);
        echo $indexing->generateSearchResultsAction($filesDataToRender, $wrdToSrc);
    }
}

if (isset($_POST['index']) && $_POST['index'] == true)
{
    // Indexing info log
    $renderData = [];
    // If paths are not empty start indexing
    if (!empty($paths))
    {
        $filesController->setFilesRepo($filesRepo);
        try {
            if(!empty($filesController->files))
            {
                $indexInfo = $indexing->excludeOrIncludeFilesToIndexAction($filesController->files, $paths);
                $filesController->setFilesMainDataAction();
                $indexing->indexAction($filesController->files);

                // Collect data to render indexing info
                $renderData[] = ['paths' => $paths, 'header' =>
                    ['header_color' => 'green', 'header_name' => 'Current directory files:']];
                $renderData[] = ['paths' => $indexInfo['new_or_mod_files'], 'header' =>
                    ['header_color' => 'blue', 'header_name' => 'New or modified files:']];
                $renderData[] = ['paths' => $indexInfo['deleted_files'], 'header' =>
                    ['header_color' => 'red', 'header_name' => 'Deleted files:']];

            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    // Rendering an info
    echo $indexing->renderMainAreaAction($renderData);
}