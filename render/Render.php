<?php

namespace render;

/**
 * Class Render
 * @package render
 */
class Render
{
    /**
     * @param array $filesData
     * @param string $wordToSrc
     * @return string
     */
    public static function generateSearchResults(array $filesData, string $wordToSrc): string
    {
        $jsonFilesData = [];

        // Anon. function to generate strings to render
        $stringsGenerator = function (array $fileStrings, string $word): array {
            $resultStrings = [];
            foreach ($fileStrings as $fileString)
            {
                $resultStrings[] = preg_replace(sprintf("/\b%s\b/i", $word),
                        sprintf("<b style='color: red'>%s</b>", $word), $fileString) . "<br/>";
            }
            return $resultStrings;
        };

        foreach ($filesData as $fileData)
        {
            $fileInfo = sprintf("<p><h3 style='color: green'>File: %s</h3></p>", $fileData['file_path']);
            $dataToJson['file_info'] = $fileInfo;
            $dataToJson['file_strings'] = $stringsGenerator($fileData['file_strings'], $wordToSrc);
            $jsonFilesData[] = $dataToJson;
        }
        return json_encode($jsonFilesData);
    }

    /**
     * @param array $renderData
     * @return string
     */
    public static function renderMainArea(array $renderData): string
    {
        // Anon. function that renders an html data of files
        $htmlGenerator = function (array $header, array $paths): string {
            $color = $header['header_color'];
            $title = $header['header_name'];
            $content = sprintf("<div class='parser-core_files_info_log'><h3 style='color:%s'>%s</h3><ul>",
                $color, $title);
            foreach ($paths as $path)
            {
                $content .= sprintf("<li>%s</li>", $path);
            }
            $content .= "</ul></div>";
            return $content;
        };

        $htmlContent = '';
        if (!empty($renderData))
        {
            foreach ($renderData as $data) {
                if (!empty($data['paths'])) {
                    $htmlContent .= $htmlGenerator($data['header'], $data['paths']);
                }
            }
        } else {
            $htmlContent .=
                "<div class='parser-core_empty'><h3>There are no files or something wrong with parser!</h3></div>";
        }
        return $htmlContent;
    }
}