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
    $renderData = [];
    // If paths are not empty start indexing
    if (!empty($paths))
    {
        $filesController->setFilesRepo($filesRepo);
        try {
            if(!empty($filesController->files))
            {
                $renderData['current_directory_files'] = $filesController->files;
                $indexing->excludeOrIncludeFilesToIndexAction($filesController->files);
                $renderData['new_files_to_index'] = $filesController->files;

                $filesController->setFilesMainDataAction();
                $indexing->indexAction($filesController->files);
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    echo $indexing->renderMainAreaAction($renderData);
}