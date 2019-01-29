<?php

namespace controller;

use core\FileCore;
use core\RepoCore;

/**
 * Class FileController
 * @package controller
 */
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
        try {

            /**@var FileCore $file */
            foreach ($this->files as $file)
            {
                $file->setFileMainData($this->filesRepo);
            }

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

}
