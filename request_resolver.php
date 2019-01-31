<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';

spl_autoload_register('autoLoader');

// Handle ajax request and invoke search action
if (isset($_POST['wordToSearch']))
{
    $wrdToSrc = $_POST['wordToSearch'];
    $indexing = new controller\IndexController();
    $paths = $indexing->readFolderAction(__DIR__ . DIRECTORY_SEPARATOR . 'texts');
    $filesController = new controller\FileController();
    $indexing->setFilesRepo(new core\RepoCore('localhost', 'root'));
    $filesController->initFilesObjects($paths);

    if (!empty($filesController->files))
    {
        $filesDataToRender = $indexing->searchAction($wrdToSrc, $filesController->files);
        $filesJsonData = $indexing->generateSearchResultsAction($filesDataToRender, $wrdToSrc);
        echo $filesJsonData;
    }
}
