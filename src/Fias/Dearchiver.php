<?php

namespace Fias;

class Dearchiver
{
    private $pathToFile;
    private $pathToFileFolder;

    public function __construct($pathToFile, $pathToFileFolder)
    {
        $this->pathToFile       = $pathToFile;
        $this->pathToFileFolder = $pathToFileFolder;

        FileHelper::isItReadable($pathToFile);
        FileHelper::isFolder($pathToFileFolder);
        FileHelper::isItWritable($pathToFileFolder);
    }

    public function extract()
    {
        // Формируем имя папки вида VersionID_DateAndTime
        $folder = $this->pathToFileFolder
            . '/'
            . explode('_', basename($this->pathToFile), 1)[0]
            . '_'
            . date('YmdHis')
        ;

        mkdir($folder);
        exec('unrar e ' . $this->pathToFile . ' ' . $folder, $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка разархивации: ' . implode("\n", $output));
        }

        return $folder;
    }
}
