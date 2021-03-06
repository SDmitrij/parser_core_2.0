<?php

namespace controller;

use core\FileCore;
use core\IndexCore;
use core\RepoCore;

/**
 * Class IndexController
 * @package controller
 */
class IndexController extends IndexCore
{

    /**
     * @param RepoCore $filesRepo
     */
    public function setFilesRepo($filesRepo)
    {
        $this->filesRepo = $filesRepo;
    }

    /**
     * @param string $dir
     * @return array
     */
    public function readFolderAction(string $dir): array
    {
        return parent::readFolder($dir);
    }

    /**
     * @param array $files
     * @throws \Exception
     */
    public function indexAction(array $files): void
    {
        /** @var  FileCore $file */
        foreach ($files as $file)
        {
            parent::indexer($file);
        }
    }

    /**
     * @param string $wordToSrc
     * @param array $files
     * @return array
     */
    public function searchAction(string $wordToSrc, array $files): array
    {
        return parent::searcher($wordToSrc, $files);
    }

    /**
     * @param array $files
     * @param array $paths
     * @return array
     * @throws \Exception
     */
    public function excludeOrIncludeFilesToIndexAction(array &$files, array $paths): array
    {
        return parent::excludeOrIncludeFilesToIndex($files, $paths);
    }
}
