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
    public function createRepo()
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
    public function createAlreadyIndexedRepo()
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
    public function createTableStrings(string $filename)
    {
        if ($this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$filename
           (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            file_unique_key VARCHAR(32) NOT NULL,
            string_of_file VARCHAR(200) NOT NULL)") == false)
        {
            throw new \Exception("Failed to create table with strings of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @throws \Exception
     */
    public function createTableWords(string $filename)
    {
        if ($this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$this->WRD_PREFIX"."$filename
           (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            file_unique_key VARCHAR(32) NOT NULL,
            word_of_file VARCHAR(50) NOT NULL)") == false)
        {
            throw new \Exception("Failed to create table with words of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filePath
     * @param string $fileHash
     * @param string $fileUniqueKey
     * @param int $fileSize
     * @param int $isIndex
     * @throws \Exception
     */
    public function
    insertIntoAlreadyIndex(string $filePath, string $fileHash, string $fileUniqueKey, int $fileSize, int $isIndex)
    {
        $filePath = $this->DB->real_escape_string(trim($filePath));

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
     * @throws \Exception
     */
    public function insertIntoTableStrings(string $filename, string $fileUniqueKey, string $strOfFile)
    {
        $strOfFile = $this->DB->real_escape_string(trim($strOfFile));

        if ($this->DB->query("INSERT INTO $this->DB_NAME.$filename
           (file_unique_key, string_of_file) VALUES ('$fileUniqueKey', '$strOfFile')") == false)
        {
            throw new \Exception("Failed to insert a string of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $word
     * @throws \Exception
     */
    public function insertIntoTableWords(string $filename, string $fileUniqueKey, string $word)
    {
        $word = $this->DB->real_escape_string($word);

        if ($this->DB->query("INSERT INTO $this->DB_NAME.$this->WRD_PREFIX"."$filename
            (file_unique_key, word_of_file) VALUES ('$fileUniqueKey', '$word')") == false)
        {
            throw new \Exception("Failed to insert a word of file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @throws \Exception
     */
    public function deleteFilesRepo(string $filename, string $fileUniqueKey)
    {
        if (($this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$filename")
            &&
            $this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$this->WRD_PREFIX"."$filename")
            &&
            $this->DB->query("DELETE FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'")) == false)
        {
            throw new \Exception("Failed to delete file: " . $filename . "<br/>");
        }
    }

    /**
     * @param string $fileUniqueKey
     * @return array
     */
    public function getFileMainData(string $fileUniqueKey): array
    {

        $query =
            $this
            ->DB
            ->query("SELECT * FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'");

        if ($query != false)
        {
            $result = $query->fetch_array(MYSQLI_ASSOC);
        } else {

            $result = [];
        }

        return $result;
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
    public function setIsIndexStatus(string $fileUniqueKey, int $indexStatus)
    {
        if ($this
            ->DB
            ->query("UPDATE $this->DB_NAME.$this->ALREADY_IDX SET is_index = $indexStatus WHERE file_unique_key = '$fileUniqueKey'") == false)
        {
            throw new \Exception("Failed to update file's status<br/>");
        }
    }

}
