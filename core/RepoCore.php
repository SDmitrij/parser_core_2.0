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
            throw new \Exception("Can't create a database\n");
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
            throw new \Exception("Can't create already indexed files table\n");
        }

    }

    /**
     * @param string $filename
     * @return bool|\mysqli_result
     */
    public function createTableStrings(string $filename)
    {
        return $this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$filename
        (id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         file_unique_key VARCHAR(32) NOT NULL,
         string_of_file VARCHAR(200) NOT NULL)");
    }

    /**
     * @param string $filename
     * @return bool|\mysqli_result
     */
    public function createTableWords(string $filename)
    {
        return $this->DB->query("CREATE TABLE IF NOT EXISTS $this->DB_NAME.$this->WRD_PREFIX.$filename
        (id INT(10) UNSIGNED AUTO INCREMENT PRIMARY KEY,
         file_unique_key VARCHAR(32) NOT NULL,
         word_of_file VARCHAR(50) NOT NULL)");
    }

    /**
     * @param string $filePath
     * @param string $fileHash
     * @param string $fileUniqueKey
     * @param int $fileSize
     * @param int $isIndex
     * @return bool|\mysqli_result
     */
    public function
    insertIntoAlreadyIndex(string $filePath, string $fileHash, string $fileUniqueKey, int $fileSize, int $isIndex)
    {
        return $this->DB->query("INSERT INTO $this->DB_NAME.$this->ALREADY_IDX
        (file_path, file_hash, file_unique_key, file_size, is_index) 
        VALUES ('$filePath', '$fileHash', '$fileUniqueKey', $fileSize, $isIndex)");
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $strOfFile
     * @return bool|\mysqli_result
     */
    public function insertIntoTableStrings(string $filename, string $fileUniqueKey, string $strOfFile)
    {
        $strOfFile = $this->DB->real_escape_string(trim($strOfFile));

        return $this->DB->query("INSERT INTO $this->DB_NAME.$filename
        (file_unique_key, string_of_file) VALUES ('$fileUniqueKey', '$strOfFile')");
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @param string $word
     * @return bool|\mysqli_result
     */
    public function insertIntoTableWords(string $filename, string $fileUniqueKey, string $word)
    {
        return $this->DB->query("INSERT INTO $this->DB_NAME.$this->WRD_PREFIX.$filename
        (file_unique_key, word_of_file) VALUES ('$fileUniqueKey', '$word')");
    }

    /**
     * @param string $filename
     * @param string $fileUniqueKey
     * @return bool|\mysqli_result
     */
    public function deleteFilesRepo(string $filename, string $fileUniqueKey)
    {
        return
            $this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$filename")
            and
            $this->DB->query("DROP TABLE IF EXISTS $this->DB_NAME.$this->WRD_PREFIX.$filename")
            and
            $this->DB->query("DELETE FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'");
    }

    /**
     * @param string $fileUniqueKey
     * @param bool $allFiles
     * @return array|mixed
     */
    public function getFilesOrFileMainData(bool $allFiles, string $fileUniqueKey = ''): array
    {
        if ($allFiles) {
            $query = $this->DB->query("SELECT * FROM $this->DB_NAME.$this->ALREADY_IDX");
        } else {
            $query =
                $this
                ->DB
                ->query("SELECT * FROM $this->DB_NAME.$this->ALREADY_IDX WHERE file_unique_key = '$fileUniqueKey'");
        }

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
     * @param int $indexStatus
     * @return bool|\mysqli_result
     */
    public function setIsIndexStatus(string $fileUniqueKey, int $indexStatus)
    {
        return
            $this
            ->DB
            ->query("UPDATE $this->DB_NAME.$this->ALREADY_IDX SET is_index = $indexStatus WHERE file_unique_key = $fileUniqueKey");
    }





}
