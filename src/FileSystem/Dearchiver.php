<?php

namespace FileSystem;

class Dearchiver
{
    public static function extract($pathToFileDirectory, $pathToFile)
    {
        static::checkPaths($pathToFileDirectory, $pathToFile);
        $directory = static::generateDirectoryName($pathToFileDirectory, $pathToFile);
        static::doExtract($directory, $pathToFile);

        return $directory;
    }

    private static function checkPaths($pathToFileDirectory, $pathToFile)
    {
        FileHelper::ensureIsReadable($pathToFile);
        FileHelper::ensureIsDirectory($pathToFileDirectory);
        FileHelper::ensureIsWritable($pathToFileDirectory);
    }

    private static function generateDirectoryName($pathToFileDirectory, $pathToFile)
    {
        // Формируем имя папки вида VersionID_DateAndTime
        return $pathToFileDirectory
            . '/'
            . explode('_', basename($pathToFile), 1)[0]
            . '_'
            . date('YmdHis')
        ;
    }

    private static function doExtract($directoryForExtract, $pathToFile)
    {
        mkdir($directoryForExtract);

        $pathToFile          = escapeshellarg($pathToFile);
        $directoryForExtract = escapeshellarg($directoryForExtract);

        exec('unrar e ' . $pathToFile . ' ' . $directoryForExtract . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка разархивации: ' . implode("\n", $output));
        }

        return $directoryForExtract;
    }
}
