<?php

namespace core;

/**
 * Class RepoCore
 * @package core
 */
class RepoCore
{
    /**
     * @var \mysqli
     */
    private $DB;

    /**
     * @var string $DB_NAME
     */
    private $DB_NAME = 'parser_core';

    /**
     * @var string $ALREADY_IDX
     */
    private $ALREADY_IDX = 'already_indexed__';

    /**
     * @var string $WRD_PREFIX
     */
    private $WRD_PREFIX = 'words_of__';

    /**
     * @var string $STR_PREFIX
     */
    private $STR_PREFIX = 'strings_of__';

    /**
     * RepoCore constructor.
     * @param string $host
     * @param string $user
     * @param string $password
     */
    public function __construct(string $host, string $user, string $password = '')
    {
        $this->DB = new \mysqli($host, $user, $password);

        try {
            $this->createRepo();
            $this->createAlreadyIndexedRepo();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

    }

    /**
     * Create main files repository
     * @throws \Exception
     */
    public function createRepo(): void
    {
        $query = $this->DB->query(" CREATE DATABASE IF NOT EXISTS " . $this->DB_NAME . "");
        if ($query == false)
        {
            throw new \Exception("Failed to create an entry database<br/>");
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function createAlreadyIndexedRepo(): void
    {
        $query =  $this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$this->ALREADY_IDX
        (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         file_path VARCHAR(100) NOT NULL,
         file_hash VARCHAR(32) NOT NULL,
         file_unique_key VARCHAR(32) NOT NULL,
         file_size INT(10) NOT NULL,
         is_index TINYINT(1) NOT NULL)");

        if ($query == false)
        {
            throw new \Exception("Failed to create table with already indexed files<br/>");
        }

    }

    /**
     * @param string $filename
     * @throws \Exception
     */
    public function createTableStrings(string $filename): void
    {
        if ($this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$this->STR_PREFIX"."$filename
           (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            file_unique_key VARCHAR(32) NOT NULL,
            string_of_file VARCHAR(200) NOT NULL,
            num_of_line INT(10) NOT NULL,
            INDEX str_idx (string_of_file))") == false)
        {
            throw new \Exception("Failed to create table with strings of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @throws \Exception
     */
    public function createTableWords(string $filename): void
    {
        if ($this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$this->WRD_PREFIX"."$filename
           (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            file_unique_key VARCHAR(32) NOT NULL,
            word_of_file VARCHAR(50) NOT NULL,
            num_of_line INT(10) NOT NULL,
            INDEX wrd_idx (word_of_file))") == false)
        {
            throw new \Exception("Failed to create table with words of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param FileCore $file
     * @throws \Exception
     */
    public function
    insertIntoAlreadyIndex($file): void
    {
        $filePath = $this->DB->real_escape_string(trim($file->getFilePath()));
        $fileHash = $file->getFileHash();
        $fileUniqueKey = $file->getFileUniqueKey();
        $fileSize = $file->getFileSize();
        $isIndex = $file->getIsIndexStatus();

        if ($this->DB->query("INSERT INTO $this->DB_NAME.$this->ALREADY_IDX
           (file_path, file_hash, file_unique_key, file_size, is_index) 
            VALUES ('$filePath', '$fileHash', '$fileUniqueKey', $fileSize, $isIndex)") == false)
        {
            throw new \Exception("Failed to write a file's data " . $filePath . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $strOfFile
     * @param int $lineNum
     * @throws \Exception
     */
    public function insertIntoTableStrings(string $filename, string $fileUniqueKey, string $strOfFile, int $lineNum): void
    {
        $strOfFile = $this->DB->real_escape_string(trim($strOfFile));

        if ($this->DB->query("INSERT INTO $this->DB_NAME.$this->STR_PREFIX"."$filename
           (file_unique_key, string_of_file, num_of_line) VALUES ('$fileUniqueKey', '$strOfFile', $lineNum)") == false)
        {
            throw new \Exception("Failed to insert a string of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $word
     * @param int $lineNum
     * @throws \Exception
     */
    public function insertIntoTableWords(string $filename, string $fileUniqueKey, string $word, int $lineNum): void
    {
        $word = $this->DB->real_escape_string($word);

        if ($this->DB->query("INSERT INTO $this->DB_NAME.$this->WRD_PREFIX"."$filename
            (file_unique_key, word_of_file, num_of_line) VALUES ('$fileUniqueKey', '$word', $lineNum)") == false)
        {
            throw new \Exception("Failed to insert a word of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @throws \Exception
     */
    public function deleteFilesRepo(string $filename, string $fileUniqueKey): void
    {
        if (($this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$this->STR_PREFIX"."$filename")
            &&
            $this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$this->WRD_PREFIX"."$filename")
            &&
            $this->DB->query("DELETE FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'")) == false)
        {
            throw new \Exception("Failed to delete file: " . $filename . "<br/>");
        }
    }

    /**
     * @param bool $allFiles
     * @param string|NULL $fileUniqueKey
     * @return array
     */
    public function getFileMainData(bool $allFiles, $fileUniqueKey = NULL): array
    {
        $rows = [];
        if ($allFiles == false) {
            $sql = "SELECT * FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'";
        } else {
            $sql = "SELECT * FROM $this->DB_NAME.$this->ALREADY_IDX";
        }
        $query = $this->DB->query($sql);
        if ($query != false)
        {
            $rows = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $result = $allFiles ? $rows : reset($rows) ;
    }

    /**
     * @param string $fileUniqueKey
     * @return bool
     */
    public function checkIfFileAlreadyIndexed(string $fileUniqueKey): bool
    {
        $check =
            $this
            ->DB
            ->query("SELECT EXISTS(SELECT id FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey')");

        $result = $check->fetch_array(MYSQLI_NUM);

        if ($result != NULL && $result[0] == '1')
        {
            return true;
        } else {
            return false;
        }


    }

    /**
     * @param string $fileUniqueKey
     * @param int $indexStatus
     * @throws \Exception
     */
    public function setIsIndexStatus(string $fileUniqueKey, int $indexStatus): void
    {
        if ($this
            ->DB
            ->query("UPDATE $this->DB_NAME.$this->ALREADY_IDX SET is_index = $indexStatus WHERE file_unique_key = '$fileUniqueKey'") == false)
        {
            throw new \Exception("Failed to update file's status<br/>");
        }
    }

    /**
     * @param string $wordToSrc
     * @param string $fileName
     * @return array
     */
    public function searchInFilesWords(string $wordToSrc, string $fileName): array
    {
        $wordToSrc = $this->DB->real_escape_string($wordToSrc);
        $query = $this->DB->query("SELECT file_unique_key FROM $this->DB_NAME.$this->WRD_PREFIX"."$fileName WHERE word_of_file = '$wordToSrc' LIMIT 1");
        $result = $query->fetch_array(MYSQLI_ASSOC);

        if ($result !== NULL)
        {

            $query = $this->DB->query("SELECT num_of_line FROM $this->DB_NAME.$this->WRD_PREFIX"."$fileName WHERE word_of_file = '$wordToSrc'");
            $numLines = [];
            for ($i = 0; $i < $query->num_rows; $i++)
            {
                $numLines[$i] = $query->fetch_array(MYSQLI_NUM)[0];
            }

            $result['num_lines'] = $numLines;

            return $result;

        } else {

            return [];
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $numLines
     * @return array
     */
    public function searchInFilesStrings(string $filename, string $fileUniqueKey, string $numLines): array
    {
        $query = $this->DB->query("SELECT string_of_file FROM $this->DB_NAME.$this->STR_PREFIX"."$filename WHERE file_unique_key = '$fileUniqueKey' AND num_of_line IN ($numLines)");
        $lines = [];
        if ($query->num_rows !== NULL)
        {
            for ($i = 0; $i < $query->num_rows; $i++)
            {
                $lines[$i] = $query->fetch_array(MYSQLI_NUM)[0];
            }

        }

        return $lines;
    }

}
