<?php

namespace Fias;

class Dearchiver
{
    public static function extract($pathToFileFolder, $pathToFile)
    {
        static::checkPaths($pathToFileFolder, $pathToFile);
        $folder = static::generateFolderName($pathToFileFolder, $pathToFile);
        static::doExtract($folder, $pathToFile);

        return $folder;
    }

    private static function checkPaths($pathToFileFolder, $pathToFile)
    {
        FileHelper::isItReadable($pathToFile);
        FileHelper::isFolder($pathToFileFolder);
        FileHelper::isItWritable($pathToFileFolder);
    }

    private static function generateFolderName($pathToFileFolder, $pathToFile)
    {
        // Формируем имя папки вида VersionID_DateAndTime
        return $pathToFileFolder
            . '/'
            . explode('_', basename($pathToFile), 1)[0]
            . '_'
            . date('YmdHis')
        ;
    }

    private static function doExtract($folderForExtract, $pathToFile)
    {
        mkdir($folderForExtract);

        $pathToFile       = escapeshellarg($pathToFile);
        $folderForExtract = escapeshellarg($folderForExtract);

        exec('unrar e ' . $pathToFile . ' ' . $folderForExtract, $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка разархивации: ' . implode("\n", $output));
        }

        return $folderForExtract;
    }
}
