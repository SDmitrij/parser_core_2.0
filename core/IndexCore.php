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
     * @throws \Exception
     */
    protected function excludeOrIncludeFilesToIndex(array &$files): void
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
                    if ($prevFileData['file_hash'] == $file->getFileHash()
                        && $prevFileData['file_size'] == $file->getFileSize())
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

    /**
     * @param array $filesData
     * @param string $wordToSrc
     * @return string
     */
    protected function generateSearchResults(array $filesData, string $wordToSrc): string
    {
        $jsonFilesData = [];

        foreach ($filesData as $fileData)
        {
            $jsonFilesData['file_info'][] = $fileData['file_path'];

            foreach ($fileData['file_strings'] as $fileString)
            {
                $jsonFilesData['file_strings'][] = preg_replace(sprintf('/\b%s\b/i', $wordToSrc),
                    sprintf("<text style='color:red'>%s</text>", $wordToSrc), $fileString) . "<br/>";
            }

        }

        return json_encode($jsonFilesData);
    }

    /**
     * @param array $renderData
     * @param string $templatePath
     * @return string
     */
    protected function renderMainArea(array $renderData, string $templatePath): string
    {
        // Anon. function that renders an html data of files
        $filesHtmlGenerator = function (string $key, array $renderData): string {

            $blockName = str_replace('_', ' ', $key);
            $content = sprintf("<div class='parser-core_%s'><h3>%s:</h3><ul>", $key, $blockName);

            foreach ($renderData[$key] as $file)
            {
                /** @var \core\FileCore $file */
                $content .= "<li>" . $file->getFilePath() . "</li>";
            }

            $content .= "</ul></div>";
            return $content;
        };

        // File's data to render
        $htmlContent = '';

        if (!empty($renderData))
        {
            $renderKeys = array_keys($renderData);
            foreach ($renderKeys as $renderKey)
            {
                if (!empty($renderData[$renderKey]))
                {
                    $htmlContent .= $filesHtmlGenerator($renderKey, $renderData);
                }

            }

        } else {

            $htmlContent .=
                "<div class='parser-core_empty'><h3>There are no files or something wrong with parser!</h3></div>";
        }

        $renderArea = str_replace('<!--Render-->', $htmlContent, file_get_contents($templatePath));

        return $renderArea;
    }
}
