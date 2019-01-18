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
     * @param object $file
     * @throws \Exception
     */
    protected function indexer(object $file): void
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

        foreach ($files as $file)
        {
            if ($this->filesRepo->checkIfFileAlreadyIndexed($file->getFileUniqueKey()) == true)
            {
                $filesStrMatchesAndKeys = $this->filesRepo->searchInFilesWords($wordToSrc, $file->getFileName());
                if (!empty($filesStrMatchesAndKeys))
                {
                    $filesData['file_path'] = $file->getFilePath();
                    $filesData['file_strings'] = $this->filesRepo->searchInFilesStrings($file->getFileName(), $filesStrMatchesAndKeys['file_unique_key'],
                        implode(',', $filesStrMatchesAndKeys['num_lines']));
                }
            }
        }

        return $filesData;
    }

    /**
     * @param array $files
     * @throws \Exception
     */
    protected function excludeOrIncludeFilesToIndex(array & $files): void
    {
        try {

            // Go through file objects
            foreach ($files as $key => $file)
            {
                if ($this->filesRepo->checkIfFileAlreadyIndexed($file->getFileUniqueKey()) == true)
                {
                    // Get prev. file's data
                    $prevFileData = $this->filesRepo->getFileMainData($file->getFileUniqueKey());

                    // If files are equal
                    if ($prevFileData['file_hash'] == $file->getFileHash() && $prevFileData['file_size'] == $file->getFileSize())
                    {
                        unset($files[$key]);
                    } else {
                        // Delete file's info 'cause it modified
                        $this->filesRepo->deleteFilesRepo($file->getFileName(), $file->getFileUniqueKey());
                    }
                }
            }

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
