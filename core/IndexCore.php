<?php

namespace core;

/**
 * Class IndexCore
 * @package core
 */
class IndexCore
{
	
    /**
     * @var RepoCore
     */
    protected $filesRepo;

    /**
     * @param string $dir
     * @return array
     */
    protected function readFolder(string $dir): array
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
                        $paths[] = $dir . DIRECTORY_SEPARATOR . $file;
                    }
                }
                closedir($dh);
            }
        }

        return $paths;
    }

    /**
     * @param FileCore $file
     * @throws \Exception
     */
    protected function indexer($file): void
    {
        // Get file's strings as array
        $lines = file($file->getFilePath());
        $lineCounter = 1;

        if ($lines !== false)
        {
            try {
                $this->filesRepo->createTableStrings($file->getFileName());
                $this->filesRepo->createTableWords($file->getFileName());
                foreach ($lines as $line)
                {
                    $this->filesRepo->insertIntoTableStrings($file->getFileName(), $file->getFileUniqueKey(), $line, $lineCounter);

                    // Get all words of file's string as array
                    $words = str_word_count($line, 1);

                    foreach ($words as $word)
                    {
                        $this->filesRepo->insertIntoTableWords($file->getFileName(), $file->getFileUniqueKey(), $word, $lineCounter);
                    }

                    $lineCounter += 1;
                }

                // Current file has already indexed
                $file->setFileRepoIsIndexStatus($this->filesRepo, 1);

            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }

    /**
     * @param string $wordToSrc
     * @param array $files
     * @return array
     */
    protected function searcher(string $wordToSrc, array $files): array
    {
        // Files data to render
        $filesData = [];

        /**@var FileCore $file*/
        foreach ($files as $file)
        {
            if ($this->filesRepo->checkIfFileAlreadyIndexed($file->getFileUniqueKey()) == true)
            {
                $filesStrMatchesAndKeys = $this->filesRepo->searchInFilesWords($wordToSrc, $file->getFileName());
                if (!empty($filesStrMatchesAndKeys))
                {
                    $filesData[] =
                        [
                            'file_path' => $file->getFilePath(),
                            'file_strings' => $this->filesRepo->searchInFilesStrings($file->getFileName(),
                                $filesStrMatchesAndKeys['file_unique_key'],
                                implode(',', $filesStrMatchesAndKeys['num_lines']))
                        ]
                    ;
                }
            }
        }

        return $filesData;
    }

    /**
     * @param array $files
     * @param array $paths
     * @return array
     * @throws \Exception
     */
    protected function excludeOrIncludeFilesToIndex(array &$files, array $paths): array
    {
        $indexInfo = ['deleted_files' => [], 'new_or_mod_files' => []];
        try {
            // Go through file objects
            /**@var FileCore $file*/
            foreach ($files as $key => $file)
            {
                if ($this->filesRepo->checkIfFileAlreadyIndexed($file->getFileUniqueKey()) == true)
                {
                    // Get prev. file's data
                    $prevFileData = $this->filesRepo->getFileMainData(false, $file->getFileUniqueKey());

                    // If files are equal
                    if ($prevFileData['file_hash'] == $file->getFileHash()
                        && $prevFileData['file_size'] == $file->getFileSize())
                    {
                        unset($files[$key]);

                    } else {
                        // Delete file's info 'cause it modified
                        $this->filesRepo->deleteFilesRepo($file->getFileName(), $file->getFileUniqueKey());
                        $indexInfo['new_or_mod_files'] = $file->getFilePath();
                    }
                }
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

        $indexInfo['deleted_files'] = $this->hardDeletedFiles($paths);
        return $indexInfo;
    }

    /**
     * @param array $paths
     * @return array
     */
    private function hardDeletedFiles(array $paths): array
    {
        $deletedFiles = [];
        try {
            $prevDirFiles = $this->filesRepo->getFileMainData(true);
            foreach ($prevDirFiles as $prevDirFile)
            {
                if (!in_array($prevDirFile['file_path'], $paths))
                {
                    $this->filesRepo->deleteFilesRepo(basename($prevDirFile['file_path'], '.txt'),
                        $prevDirFile['file_unique_key']);
                    $deletedFiles[] = $prevDirFile['file_path'];
                }
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

        return $deletedFiles;
    }
}
