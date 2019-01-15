<?php

namespace core;

/**
 * Class FileCore
 * @package core
 */
class FileCore
{
    /**
     * @var string $filePath
     */
    private $filePath;

    /**
     * @var $fileName
     */
    private $fileName;

    /**
     * @var string $fileHash
     */
    private $fileHash;

    /**
     * @var string $fileUniqueKey
     */
    private $fileUniqueKey;

    /**
     * @var int $fileSize
     */
    private $fileSize;

    /**
     * @var int $isIndex
     */
    private $isIndex = 0;

    /**
     * FileCore constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->filePath = $path;
        $this->fileName = basename($this->filePath,'.txt');
        $this->fileHash = hash_file('md5', $this->filePath);
        $this->fileUniqueKey = hash('md5', $this->filePath);
        $this->fileSize = filesize($this->filePath);
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFileUniqueKey(): string
    {
        return $this->fileUniqueKey;
    }

    /**
     * @return string
     */
    public function getFileHash(): string
    {
        return $this->fileHash;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @param object $filesRepo
     * @throws \Exception
     */
    public function setFileMainData(object & $filesRepo)
    {
        $filesRepo
            ->insertIntoAlreadyIndex($this->filePath, $this->fileHash, $this->fileUniqueKey, $this->fileSize, $this->isIndex);

    }

    /**
     * @param object $filesRepo
     * @param int $isIndex
     */
    public function setFileRepoIsIndexStatus(object & $filesRepo, int $isIndex)
    {
        $this->isIndex = $isIndex;
        $filesRepo->setIsIndexStatus($this->fileUniqueKey, $isIndex);
    }

}
