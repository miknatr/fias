<?php

namespace FileSystem;

class Directory
{
    private $directoryPath;

    public function __construct($path)
    {
        FileHelper::ensureIsReadable($path);
        FileHelper::ensureIsDirectory($path);

        $this->directoryPath = $path;
    }

    public function getVersionId()
    {
        $prefix = 'VERSION_ID_';
        return str_replace($prefix, '', $this->find($prefix));
    }

    public function getDeletedAddressObjectFile()
    {
        $fileName = $this->find('AS_DEL_ADDROBJ', false);
        return $fileName ? $this->directoryPath . '/' . $fileName : null;
    }

    public function getDeletedHouseFile()
    {
        $fileName = $this->find('AS_DEL_HOUSE_', false);
        return $fileName ? $this->directoryPath . '/' . $fileName : null;
    }

    public function getAddressObjectFile()
    {
        return $this->directoryPath . '/' . $this->find('AS_ADDROBJ');
    }

    public function getHouseFile()
    {
        return $this->directoryPath . '/' . $this->find('AS_HOUSE_');
    }

    public function getPath()
    {
        return $this->directoryPath;
    }

    private function find($prefix, $isIndispensable = true)
    {
        $files = scandir($this->directoryPath);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (mb_strpos($file, $prefix) === 0) {
                return $file;
            }
        }

        if ($isIndispensable) {
            throw new FileException('Файл с префиксом ' . $prefix . ' не найден в директории: ' . $this->directoryPath);
        }

        return null;
    }
}
