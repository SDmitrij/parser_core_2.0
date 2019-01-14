<?php

namespace controller;

use core\IndexCore;

class IndexController extends IndexCore
{
    /**
     * @param object $filesRepo
     */
    public function setFilesRepo(object $filesRepo)
    {
        $this->filesRepo = $filesRepo;
    }

    /**
     * @param string $dir
     * @return array
     */
    public function readFolder(string $dir): array
    {
        $paths = [];
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while ($file = readdir($dh))
                {
                    if ($file != "." && $file != "..")
                    {
                        $paths[] = $dir . "/" . $file;
                    }
                }
                closedir($dh);
            }
        }

        return $paths;
    }

    /**
     * @param array $files
     * @throws \Exception
     */
    public function indexAction(array $files)
    {
        foreach ($files as $file)
        {
            parent::indexer($file);
        }
    }

    public function searchAction()
    {
        parent::searcher();
    }

    /**
     * @param array $files
     * @throws \Exception
     */
    public function excludeOrIncludeFilesToIndexAction(array & $files)
    {
        parent::excludeOrIncludeFilesToIndex($files);
    }

}
