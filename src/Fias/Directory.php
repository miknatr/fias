<?php

namespace Fias;

class Directory
{
    private $directoryPath;

    public function __construct($path)
    {
        FileHelper::ensureIsReadable($path);
        FileHelper::ensureIsDirectory($path);

        $this->directoryPath = $path;
    }

    public function getAddressObjectFile()
    {
        return $this->find('AS_ADDROBJ');
    }

    public function getHousesFile()
    {
        return $this->find('AS_HOUSE');
    }

    public function getPath()
    {
        return $this->directoryPath;
    }

    private function find($prefix)
    {
        $files = scandir($this->directoryPath);
        foreach ($files as $file) {
            if (in_array($file, array('.', '..'))) {
                continue;
            }

            if (mb_strpos($file, $prefix) === 0) {
                return $file;
            }
        }

        throw new FileException('Файл с префиксом ' . $prefix . ' не найден в директории: ' . $this->directoryPath);
    }
}
