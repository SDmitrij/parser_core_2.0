<?php

/**
 * I know that this file must be slim, but I have a lack of time to write router and etc...
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';

spl_autoload_register('autoLoader');

// Init indexing process
$indexing = new \controller\IndexController();
$paths = $indexing->readFolderAction(__DIR__ . DIRECTORY_SEPARATOR . 'texts');
$renderData = [];
// If paths are not empty start indexing
if (!empty($paths))
{
    $filesRepo = new \core\RepoCore('localhost', 'root');
    $filesController = new \controller\FileController();
    $indexing->setFilesRepo($filesRepo);
    $filesController->initFilesObjects($paths);
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

echo $indexing->renderMainAreaAction($renderData, __DIR__ . DIRECTORY_SEPARATOR . 'main_template.html');
