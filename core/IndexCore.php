<?php

namespace core;

class IndexCore
{
    /**
     * @var RepoCore
     */
    protected $filesRepo;

    /**
     * @param object $file
     * @throws \Exception
     */
    protected function indexer(object $file)
    {
        // Get file's strings as array
        $lines = file($file->getFilePath());

        if ($lines !== false)
        {
            try {
                $this->filesRepo->createTableStrings($file->getFileName());
                $this->filesRepo->createTableWords($file->getFileName());
                foreach ($lines as $line)
                {
                    $this->filesRepo->insertIntoTableStrings($file->getFileName(), $file->getFileUniqueKey(), $line);

                    // Get all words of file's string as array
                    $words = str_word_count($line, 1);

                    foreach ($words as $word)
                    {
                        $this->filesRepo->insertIntoTableWords($file->getFileName(), $file->getFileUniqueKey(), $word);
                    }
                }

                // Current file has already indexed
                $file->setFileRepoIsIndexStatus($this->filesRepo, 1);

            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }

    /**
     * @param array $files
     */
    protected function excludeOrIncludeFilesToIndex(array $files)
    {
        foreach ($files as $file)
        {
            if ($this->filesRepo->checkIfFileAlreadyIndexed($file->getFileUniqueKey()) == true)
            {
                // Get prev. file's data
                $prevFileData = $this->filesRepo->getFileMainData($file->getFileUniqueKey());
                if ($prevFileData['file_hash'] == $file->getFileHash()
                    && $prevFileData['file_size'] == $file->getFileSize())
                {
                    unset($file);
                }
            }
        }
    }




}
