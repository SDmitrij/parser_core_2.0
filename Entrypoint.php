<?php
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

$indexing = new \controller\IndexController();
$paths = $indexing->readFolder($dir);

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
            $indexing->excludeOrIncludeFilesToIndexAction($filesController->files);
            $filesController->setFilesMainDataAction();
            $indexing->indexAction($filesController->files);
        }

    } catch (\Exception $exception) {
        echo $exception->getMessage();
    }

}

