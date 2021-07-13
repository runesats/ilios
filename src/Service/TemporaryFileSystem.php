<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;

/**
 * Class FileSystem
 *
 */
class TemporaryFileSystem
{
    /**
     * @var string
     */
    protected $temporaryFileStorePath;

    /**
     * A filesystem object to work with
     * @var FileSystem
     */
    protected $fileSystem;

    public function __construct(SymfonyFileSystem $fs, $kernelProjectDir)
    {
        $tmpPath = realpath($kernelProjectDir . '/var/tmp');
        $this->fileSystem = $fs;
        $this->temporaryFileStorePath = $tmpPath . '/uploads';
        if (!$this->fileSystem->exists($this->temporaryFileStorePath)) {
            $this->fileSystem->mkdir($this->temporaryFileStorePath);
        }
        $this->fileSystem = $fs;
    }

    /**
     * Store a file and return the hash
     * @return string $hash
     */
    public function storeFile(File $file)
    {
        $hash = md5_file($file->getPathname());
        if (!$this->fileSystem->exists($this->getPath($hash))) {
            $this->fileSystem->rename(
                $file->getPathname(),
                $this->getPath($hash)
            );
        }

        return $hash;
    }

    /**
     * Create a temporary file from a string
     */
    public function createFile(string $contents): File
    {
        $hash = md5($contents);
        $path = $this->getPath($hash);
        if (!$this->fileSystem->exists($path)) {
            $this->fileSystem->dumpFile($path, $contents);
        }

        return $this->getFile($hash);
    }

    /**
     * Remove a file from the file system by hash
     * @param string $hash
     */
    public function removeFile($hash)
    {
        $this->fileSystem->remove($this->getPath($hash));
    }

    /**
     * Get a File from a hash
     * @param string $hash
     * @return File|bool
     */
    public function getFile($hash)
    {
        if ($this->fileSystem->exists($this->getPath($hash))) {
            return new File($this->getPath($hash));
        }

        return false;
    }

    /**
     * Turn a relative path into an ilios file store path
     * @param  string $hash
     * @return string
     */
    protected function getPath($hash)
    {
        return $this->temporaryFileStorePath . '/' . $hash;
    }
}
