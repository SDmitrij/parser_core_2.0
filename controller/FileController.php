<?php

namespace controller;

use core\FileCore;
use core\RepoCore;

class FileController
{
    /**
     * @var RepoCore
     */
    public $filesRepo;

    /**
     * @var array $files
     */
    public $files = [];

    /**
     * @param object $filesRepo
     */
    public function setFilesRepo(object $filesRepo)
    {
        $this->filesRepo = $filesRepo;
    }

    /**
     * @param array $paths
     */
    public function initFilesObjects(array $paths)
    {
        foreach ($paths as $path)
        {
            $this->files[] = new FileCore($path);
        }
    }

    /**
     * @throws \Exception
     */
    public function setFilesMainDataAction()
    {
        foreach ($this->files as $file)
        {
            if ($file->setFileMainData($this->filesRepo) == false)
            {
                throw new \Exception("Can't write a main information of " . $file->getFilePath() . "\n");
            }
        }

    }

}
