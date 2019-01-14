<?php
require_once __DIR__ . '\render_helper.php';
function autoLoader($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;
}
spl_autoload_register('autoLoader');

// Our directory with files to index
$dir = __DIR__ . '\texts';



// Init indexing process
$indexing = new \controller\IndexController();
$paths = $indexing->readFolder($dir);
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

// Render html
renderMainArea($renderData);
